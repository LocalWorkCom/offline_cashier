<script>
    // Pass statuses and orders from Blade to JavaScript
    const statuses = {
        Delivery: {!! json_encode(array_keys($deliveryStatuses)) !!}, // Statuses for delivery orders
        Takeaway: {!! json_encode(array_keys($takeawayStatuses)) !!}, // Statuses for takeaway orders
    };

    const orders = {!! json_encode(
        $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'client_id' => $order->client_id,
                    'type' => $order->type,
                ];
            })->toArray(),
    ) !!};

    // Map status IDs to status texts
    const statusMap = {
        1: 'pending',
        2: 'in_progress',
        3: 'on_way',
        4: 'delivered',
        5: 'completed',
    };
    $(document).ready(function() {
        // Subscribe to channels for each order
        if (orders && orders.length > 0) {
            orders.forEach(order => {
                const channelName =
                    `order-channel-${order.id}-client-${order.client_id}`;
                const channel = pusher.subscribe(channelName);

                channel.bind('order-status-update', function(data) {
                    console.log('Order #' + data.orderId + ' status changed to: ' + data
                        .status);
                    const statusText = statusMap[data.status];
                    console.log('Mapped status text: ', statusText);

                    const orderStatuses = statuses[order.type];
                    updateOrderStatusUI(order.id, statusText, orderStatuses);
                    if (statusText === 'on_way' && data.channel) {
                        // Show the contactDriver section
                        $(`#contactDriver-${order.id}`).removeClass('d-none');
                        $(`#contactRestaurant-${order.id}`).addClass('d-none');
                        // Update driver image, name, and phone dynamically (if needed)
                        const imgSrc =
                            '/front/AlKout-Resturant/SiteAssets/images/delivery-man.png';

                        $(`#contactDriver-${order.id} .contact-img`).attr('src', imgSrc);
                        $(`#contactDriver-${order.id} .courier-content span`).text(
                            `${data.channel.delivery_name}`);

                        // Update phone in modal
                        $(`#captainContact-${order.id} .modal-footer p`).text(data.channel
                            .delivery_phone);
                        $(`#captainContact-${order.id} .modal-footer div`).attr('onclick',
                            `copyToClipboard('${data.channel.delivery_phone}')`);

                        // Update chat box image/name (if chat opened later)
                        const chatBox = $('#driverChatBox');
                        if (chatBox.length) {
                            chatBox.find('.user-img img').attr('src', imgSrc);
                            chatBox.find('.user-content h6').text(
                                `${data.channel.delivery_name}`);
                        }
                    }
                });
            });
        }

        function toggleDriverChat(orderId) {
            $(`#driverChatBox-${orderId}`).toggle();
        }
    });
</script>
<script>
    // Function to update the UI based on the new status
    function updateOrderStatusUI(orderId, status, orderStatuses) {
        // Find the timeline for the specific order
        const timeline = document.querySelector(`#order-${orderId} .timeline`);

        if (timeline) {
            // Iterate through timeline items and update their classes
            orderStatuses.forEach((key, index) => {
                const stepElement = timeline.querySelector(`#step${index}-tab${orderId}`);
                if (stepElement) {
                    stepElement.classList.remove('active', 'completed', 'disabled');

                    if (key === status) {
                        stepElement.classList.add('active'); // Current status
                    } else if (orderStatuses.indexOf(key) < orderStatuses.indexOf(status)) {
                        stepElement.classList.add('completed'); // Completed status
                    } else {
                        stepElement.classList.add('disabled'); // Upcoming status
                    }
                }
            });
        }
    }
</script>
<script>
    $(document).ready(function() {
        // Variables
        let selectedFile = null;
        let pusherChannel = null;
        let chatChannelId = null;
        let unreadMessages = 0;
        let isUserInteracted = false;

        const clientId = {{ auth('client')->user()->id }};
        const csrfToken = '{{ csrf_token() }}';
        const isChatButtonVisible = localStorage.getItem('driverChatButtonVisible') === 'true';

        // Scroll chat container to the bottom
        function scrollToBottom() {
            const container = $('#driverChatMessages');
            container.scrollTop(container[0].scrollHeight);
        }

        // Load chat messages for a given channel ID
        function loadChatMessages(channelId) {
            $.ajax({
                url: `/chat/messages/${channelId}`,
                type: 'GET',
                success: function(response) {
                    $('#driverChatMessages').empty();
                    const messages = response.data?.messages || [];

                    messages.forEach(msg => {
                        const isMe = msg.sender_id === clientId;
                        renderMessage(msg, isMe, false);
                    });

                    scrollToBottom();
                },
                error: function(xhr) {
                    console.error('Failed to load messages:', xhr.responseText);
                }
            });
        }

        // Determine file type based on URL extension or fallback to 'unknown'
        function getFileTypeFromUrl(url) {
            if (!url || typeof url !== 'string') return 'unknown';

            const extension = url.split('.').pop().toLowerCase();
            const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            const videoExtensions = ['mp4', 'mov', 'avi', 'webm', 'mkv', 'flv'];

            if (imageExtensions.includes(extension)) return 'image';
            if (videoExtensions.includes(extension)) return 'video';

            return 'unknown';
        }

        // Render a single message in the chat box
        function renderMessage(msg, isMe, scroll = true) {
            const wrapper = $('<div>').addClass('message').addClass(isMe ? 'user-message' : 'bot-message');

            // Message content (image, video, or text)
            const fileType = msg.type || getFileTypeFromUrl(msg.content);

            if (fileType === 'image') {
                wrapper.append(
                    `<img src="${msg.content}" alt="Image" style="max-width: 200px; max-height: 200px;">`
                );
            } else if (fileType === 'video') {
                wrapper.append(
                    `<video controls style="max-width: 200px; max-height: 200px;">
                <source src="${msg.content}" type="video/mp4">
            </video>`
                );
            } else {
                wrapper.append(`<span>${msg.content}</span>`);
            }

            // Append sender image at the end of message
            const defaultImage = '{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}';
            const senderImage = isMe ? (msg.sender_image || defaultImage) : (msg.sender_image || defaultImage);
            // (You can customize sender_image to be from msg.sender_image or passed data)

            wrapper.append(
                `<img src="${senderImage}" alt="${isMe ? 'You' : 'Bot'}">`
            );

            $('#driverChatMessages').append(wrapper);
            if (scroll) scrollToBottom();
        }


        // Send message (text and/or file)
        function sendMessage() {
            const message = $('#driverChatInput').val().trim();
            if (!message && !selectedFile) return;

            const formData = new FormData();
            formData.append('message', message);
            formData.append('channel_id', chatChannelId);
            formData.append('_token', csrfToken);

            if (selectedFile) formData.append('file', selectedFile);

            $.ajax({
                url: "{{ route('chat.sender') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#driverChatInput').val('');
                    $('#driverRemoveMedia').click(); // Clear media preview
                    // renderMessage(res.message, true);
                    scrollToBottom();
                },
                error: function(err) {
                    alert('Failed to send message');
                    console.error(err);
                }
            });
        }

        // Setup Pusher channel and bind events
        function setupPusherChannel() {
            if (!chatChannelId) return;

            if (pusherChannel) {
                pusherChannel.unbind_all();
                pusher.unsubscribe(pusherChannel.name);
            }

            pusherChannel = pusher.subscribe('chat-' + chatChannelId);

            pusherChannel.bind('chatMessage', function(data) {
                const isMe = data.sender.id === clientId;

                // Build message object matching loadChatMessages' format
                const msg = {
                    sender_id: data.sender.id,
                    sender_name: data.sender.name,
                    sender_image: data.sender.image,
                    receiver_id: data.receiver.id,
                    receiver_name: data.receiver.name,
                    receiver_image: data.receiver.image,
                    type: data.type,
                    content: data.message,
                    is_read: data.is_read,
                    sent_at: data.sent_at
                };

                renderMessage(msg, isMe);

                // Check if chatbox is closed/minimized and message is not from me
                if (!$('#sharedDriverChatBox').hasClass('show') && !isMe) {
                    // Open the chatbox automatically
                    $('#sharedDriverChatBox').removeClass('d-none').addClass('show');
                    $('#driverChatButton').removeClass('minimized');

                    // Update UI to show this is the active chat
                    $('#driverName').text(msg.sender_name);
                    $('#driverImage, #driverWelcomeImage, #driverChatButtonImage').attr('src', msg
                        .sender_image);

                    unreadMessages = 0;
                    if (isUserInteracted) playNotificationSound();
                }
            });


            pusherChannel.bind('pusher:subscription_succeeded', function() {
                console.log('Subscribed to chat channel successfully');
            });
        }

        // Show media preview (image or video)
        function showMediaPreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (file.type.startsWith('image')) {
                    $('#driverPreviewImage').attr('src', e.target.result).show();
                    $('#driverPreviewVideo').hide();
                } else {
                    $('#driverPreviewVideo').attr('src', e.target.result).show();
                    $('#driverPreviewImage').hide();
                }
                $('#driverMediaPreview, #driverRemoveMedia').show();
            };
            reader.readAsDataURL(file);
        }

        // Play notification sound on new message
        function playNotificationSound() {
            const audio = new Audio('{{ asset('notification.mp3') }}');
            audio.play();
        }

        // Public API to start chat with a driver
        window.startDriverChat = function(orderId, driverName, driverImage) {
            $('#driverChatButton').removeClass('d-none').addClass('visible minimized');


            $('#driverChatButtonImage').attr('src', driverImage);
            openDriverChat(orderId, driverName, driverImage);
        };
        // Check if page was opened from a notification
        document.addEventListener('DOMContentLoaded', function() {
            // You might need to adjust this based on how your app handles URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const notificationAction = urlParams.get('action');

            if (notificationAction === 'open_chat' && orderId) {
                // Open the chat automatically
                window.openDriverChat(orderId, driverName, driverImage);
            }
        });
        // Public API to open driver chat UI and load messages
        window.openDriverChat = function(orderId, driverName, driverImage) {
            // If already in this chat, just show it
            if (chatChannelId === orderId && $('#sharedDriverChatBox').hasClass('show')) {
                return;
            }

            fetch(`/chat-channel/${orderId}`)
                .then(response => response.json())
                .then(data => {
                    chatChannelId = data.channel.chat_channel_id;
                    setupPusherChannel();
                    loadChatMessages(chatChannelId);
                });

            $('#driverChatButton').removeClass('d-none').addClass('visible minimized');
            $('#sharedDriverChatBox').removeClass('d-none').addClass('show');
            $('#driverName').text(driverName);
            $('#driverImage, #driverWelcomeImage ,#driverChatButtonImage').attr('src', driverImage);
            $('#driverChatInput').val('');
            scrollToBottom();
            unreadMessages = 0;
        };

        // Close chat UI and hide chat button
        window.closeDriverChat = function() {
            $('#sharedDriverChatBox').removeClass('show').addClass('d-none');
            $('#driverChatButton').addClass('d-none').removeClass('visible minimized');
            localStorage.setItem('driverChatButtonVisible', 'false');
        };

        // Minimize chat UI but keep button visible
        window.minimizeDriverChat = function() {
            $('#sharedDriverChatBox').removeClass('show');
            $('#driverChatButton').removeClass('d-none').addClass('visible minimized');
            localStorage.setItem('driverChatButtonVisible', 'true');
        };

        // Event handlers
        $('#driverAttachFile').click(() => $('#driverFileInput').click());

        $('#driverFileInput').change(function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/quicktime'];
            if (!validTypes.includes(file.type)) {
                alert('Only images (JPEG, PNG, GIF) and videos (MP4, MOV) are allowed');
                $(this).val('');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                alert('File size should be less than 10MB');
                $(this).val('');
                return;
            }

            selectedFile = file;
            showMediaPreview(file);
        });

        $('#driverRemoveMedia').click(function() {
            selectedFile = null;
            $('#driverFileInput').val('');
            $('#driverMediaPreview').hide();
            $('#driverPreviewImage, #driverPreviewVideo').attr('src', '').hide();
            $(this).hide();
        });

        $('#driverEmojiBtn').click(function(e) {
            e.stopPropagation();
            $('#driverEmojiPicker').toggle();
        });

        $('#driverEmojiPicker').on('emoji-click', function(event) {
            const currentInput = $('#driverChatInput').val();
            $('#driverChatInput').val(currentInput + event.originalEvent.detail.unicode);
            $('#driverEmojiPicker').hide();
        });

        $('#driverSend').click(sendMessage);

        $('#driverChatInput').keypress(function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        $('#driverChatBox, #driverChatButton').on('click', function() {
            if (!isUserInteracted) isUserInteracted = true;
        });

        // Initialize chat button visibility based on localStorage
        if (isChatButtonVisible) {
            $('#driverChatButton').removeClass('d-none').addClass('visible minimized');
        } else {
            $('#driverChatButton').addClass('d-none').removeClass('visible minimized');
        }
    });
</script>



<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('تم نسخ الرقم: ' + text);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }
</script>
