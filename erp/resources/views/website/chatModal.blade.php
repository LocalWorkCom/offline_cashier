@auth('client')
    <style>
        @keyframes slowVibrate {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-3px);
            }

            50% {
                transform: translateX(3px);
            }

            75% {
                transform: translateX(-2px);
            }
        }

        .slow-vibrate {
            animation: slowVibrate 0.6s ease-in-out;
        }
    </style>
    <!-- Chat button will only show for authenticated clients -->
    <button class="chat-button bg-success" onclick="toggleChat()" id="chatButton">
        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/chat-icon.svg') }}" alt="" class="default-icon" />
        <span class="default-text">@lang('chat.startchat')</span>
        {{-- <img src="{{ $chat_receiver->image ?? asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}" alt="User"
            class="user-icon d-none" /> --}}
    </button>

    <div class="chat-box" id="chatBox">
        <div class="chat-header">
            <div class="d-flex align-items-center">
                <div class="user-img">
                    <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}" alt="User">
                </div>
                <div class="user-content mx-2">
                    <h6 class="fw-bold">@lang('chat.customer_service') </h6>
                    <small class="text-muted"></small>
                </div>
            </div>
            <audio id="newMessageSound" src="{{ asset('front/AlKout-Resturant/SiteAssets/new-message.mp3') }}"
                preload="auto"></audio>

            <div class="header-buttons">
                <button id="minimize"><i class="fas fa-window-minimize main-color"></i></button>
                <button onclick="closeChat()"><i class="fas fa-times-circle main-color fs-3"></i></button>
            </div>
        </div>

        <div class="chat-messages" id="chatMessages">
            <div class="bot-message message">

                    <span>@lang('chat.howcanhelp')</span>
                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}"
                    alt="Bot">
            </div>
            @foreach ($chat_messages as $message)
                @if ($message->sender != auth('client')->user()->id)
                    <div class="bot-message message">
                        @if ($message->media)
                            @if (str_contains($message->media, 'images'))
                                <img src="{{ asset($message->media) }}" alt="Image"
                                    style="max-width: 200px; max-height: 200px;">
                            @else
                                <video controls style="max-width: 200px; max-height: 200px;">
                                    <source src="{{ asset($message->media) }}" type="video/mp4">
                                </video>
                            @endif
                        @else
                            <span>{{ $message->message }}</span>
                        @endif
                        <img src="{{ $chat_receiver->image ?? asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}"
                            alt="Bot">
                    </div>
                @else
                    <div class="user-message message">
                        @if ($message->media)
                            @if (str_contains($message->media, 'images'))
                                <img src="{{ asset($message->media) }}" alt="Image"
                                    style="max-width: 200px; max-height: 200px;">
                            @else
                                <video controls style="max-width: 200px; max-height: 200px;">
                                    <source src="{{ asset($message->media) }}" type="video/mp4">
                                </video>
                            @endif
                        @else
                            <span>{{ $message->message }}</span>
                        @endif
                        <img src="{{ $chat_sender->image ?? asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}"
                            alt="You">
                    </div>
                @endif
            @endforeach
        </div>

        <div class="chat-input">
            <!-- Hidden file input -->
            <input type="file" id="fileInput" accept="image/*,video/*" style="display: none;">

            <!-- File attachment button -->
            <a href="#" class="mx-1" id="attachFile">
                <img class="chat-icon" src="{{ asset('front/AlKout-Resturant/SiteAssets/images/paperclip.svg') }}"
                    alt="" />
            </a>
            <div class="emoji-picker-container">
                <a type="button" class="mx-1 emoji-btn" id="emojiBtn1">
                    <img class="chat-icon" src="{{ asset('front/AlKout-Resturant/SiteAssets/images/emoji.svg') }}"
                        alt="@lang('chat.emoji')" />
                </a>
                <emoji-picker id="emojiPicker1" style="display: none;"></emoji-picker>
            </div>
            <!-- Send button -->
            <a href="#" class="mx-1" id="send">
                <img class="chat-icon" src="{{ asset('front/AlKout-Resturant/SiteAssets/images/send.svg') }}"
                    alt="" />
            </a>

            <!-- Text input -->
            <input type="text" id="chatInput" class="form-control" placeholder="اكتب رسالة...">
        </div>

        <!-- Preview container -->
        <div id="mediaPreview" class="mt-2" style="display: none;">
            <div class="d-flex align-items-center">
                <img id="previewImage" src="" style="max-height: 100px; display: none;">
                <video id="previewVideo" controls style="max-height: 100px; display: none;"></video>
                <button id="removeMedia" class="btn btn-sm btn-danger ms-2" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <script>

        const picker = document.querySelector('emoji-picker');
        let isUserInteracted = false;
        let unreadMessages = {{ $unreadMessages }};
        $(document).ready(function() {
            $('#chatBox').on('click', function() {
                console.log('Chat box clicked');
                console.log(unreadMessages);

                if (unreadMessages > 0) {
                    markMessagesAsRead();
                }
            });
        });

        // Only set isUserInteracted when user interacts with chat specifically
        $('#chatButton, #chatBox').on('click', function() {
            if (!isUserInteracted) {
                isUserInteracted = true;
                console.log('Chat interaction detected - sounds enabled');
            }

        });
        // Toggle emoji picker visibility
        $('#emojiBtn1').click(function(e) {
            e.stopPropagation();
            $('#emojiPicker1').toggle();

        });
        let currentUrl = window.location.pathname;

        if (!currentUrl.includes('/chat')) {
            // State Management
            let selectedFile = null;
            let pusherChannel = null;
            // Initialize when document is ready
            $(document).ready(function() {
                // Initialize Pusher
                // Subscribe to channel
                pusherChannel = pusher.subscribe('chat-{{ $chat_channel->id }}');

                // Scroll to bottom initially
                scrollToBottom();
                // Handle emoji selection
                picker.addEventListener('emoji-click', event => {
                    const currentInput = $('#chatInput').val();
                    $('#chatInput').val(currentInput + event.detail.unicode);
                    $('#emojiPicker1').hide();
                });
                // File Attachment Handling
                $('#attachFile').click(() => $('#fileInput').click());

                $('#fileInput').change(function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4',
                        'video/quicktime'
                    ];
                    if (!validTypes.includes(file.type)) {
                        alert('Only images (JPEG, PNG, GIF) and videos (MP4, MOV) are allowed');
                        return;
                    }

                    // Validate file size (10MB max)
                    if (file.size > 10 * 1024 * 1024) {
                        alert('File size should be less than 10MB');
                        return;
                    }

                    selectedFile = file;
                    showMediaPreview(file);
                });

                // Send Message (click or enter key)
                $('#send').click(sendMessage);
                $('#chatInput').keypress(function(e) {
                    if (e.which == 13) sendMessage();
                });

                // Pusher Message Handler
                pusherChannel.bind('chatMessage', function(data) {
                    renderMessage(data, data.sender.id == "{{ auth('client')->user()->id }}");
                    scrollToBottom();
                    // Only notify if chat is minimized
                    if (!$('#chatBox').hasClass('show')) {
                        unreadMessages++;
                        updateUnreadBadge();

                        // Play sound only if user has interacted with chat
                        if (isUserInteracted) {
                            playNotificationSound();
                        }
                    }
                    // // Show notification if chat is minimized
                    // if (!$('#chatBox').hasClass('show')) {
                    //     $('#chatButton').addClass('has-new-message');
                    //     $('.default-text').text('New Message!');
                    // }
                });
            });
            // Separate function for playing sound
            function playNotificationSound() {
                const sound = document.getElementById('newMessageSound');
                if (sound) {
                    sound.currentTime = 0; // Rewind to start
                    sound.play()
                        .then(() => console.log('Notification sound played'))
                        .catch(e => console.log('Sound playback prevented:', e));
                }
            }

            function updateUnreadBadge() {
                if (unreadMessages > 0) {
                    $('#chatButton').addClass('has-new-message');
                    $('.default-text').text(`New Messages (${unreadMessages})`);
                } else {
                    $('#chatButton').removeClass('has-new-message');
                    $('.default-text').text("@lang('chat.startchat')");
                }
            }
            // Chat toggle functions
            function toggleChat() {
                const chatBox = $('#chatBox');
                const isOpen = chatBox.hasClass('show');

                if (isOpen) {
                    minimizeChat();
                } else {
                    chatBox.addClass('show');
                    $('#chatButton').removeClass('has-new-message');
                    $('.default-text').text("@lang('chat.startchat')");
                    scrollToBottom();
                }

                // Mark messages as read whether you open or already open
                if (unreadMessages > 0) {
                    markMessagesAsRead();
                }
            }

            function markMessagesAsRead() {

                // Update server and local state
                $.ajax({
                    url: "{{ route('chat.markAsRead') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        channel_id: "{{ $chat_channel->id }}"
                    },
                    success: function() {
                        console.log(0);
                        unreadMessages = 0;
                        localStorage.setItem('chatUnreadMessages', 0);
                        updateUnreadBadge();
                    },
                    error: function(xhr) {
                        console.error('Failed to mark messages as read:', xhr.responseText);
                    }
                });
            }
            // function toggleChat() {
            //     const chatBox = $('#chatBox');
            //     if (chatBox.hasClass('show')) {
            //         minimizeChat();
            //     } else {
            //         chatBox.addClass('show');
            //         $('#chatButton').removeClass('has-new-message');
            //         $('.default-text').text("@lang('chat.startchat')");
            //         scrollToBottom();
            //     }
            // }

            function minimizeChat() {
                $('#chatBox').removeClass('show');
                // $('.default-icon').addClass('d-none');
                // $('.default-text').addClass('d-none');
                // $('.chat-close-btn').removeClass('d-none');
                // $('.user-icon').removeClass('d-none');
            }

            function closeChat() {
                $('#chatBox').removeClass('show');
                $('.default-icon').removeClass('d-none');
                $('.default-text').removeClass('d-none');
                $('.chat-close-btn').addClass('d-none');
                $('.user-icon').addClass('d-none');
            }

            // Send Message Function
            function sendMessage() {
                const message = $('#chatInput').val().trim();

                if (!message && !selectedFile) {
                    alert('Please enter a message or attach a file');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', "{{ csrf_token() }}");
                if (message) formData.append('message', message);
                if (selectedFile) formData.append('media', selectedFile);
                formData.append('sender_guard', 'client');
                formData.append('guard_type', 'customer_service');
                formData.append('channel_id', "{{ $chat_channel->id }}");
                formData.append('user_id', "{{ $chat_receiver->id }}");
                formData.append('type', 'web');

                // Optimistic update for text messages
                if (message && !selectedFile) {
                    const senderMessage = `
            <div class="user-message message">
                <span>${message}</span>
                <img src="{{ $chat_sender->image ?? asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}" alt="You">
            </div>`;
                    // $('#chatMessages').append(senderMessage);
                    $('#chatInput').val('');
                    scrollToBottom();
                }

                // For files, we'll wait for server response to show them
                $.ajax({
                    url: "{{ route('chat.sender') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (selectedFile) {
                            selectedFile = null;
                            $('#mediaPreview').hide();
                            $('#fileInput').val('');
                            $('#chatInput').val('');
                        }
                    },
                    error: function(xhr) {
                        alert('Failed to send message: ' + (xhr.responseJSON?.message || 'Server error'));
                    }
                });
            }

            // Render Incoming Message
            function renderMessage(data, isMe) {
                let messageContent;

                const defaultImage = "{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}";
                const senderImage = data.sender.image ? data.sender.image : defaultImage;

                if (data.type === 'text') {
                    messageContent = `<span>${data.message}</span>`;
                } else if (data.type === 'image') {
                    messageContent = `<img src="${data.message}" alt="image" style="max-width: 200px; max-height: 200px;">`;
                } else {
                    messageContent = `
            <video controls style="max-width: 200px; max-height: 200px;">
                <source src="${data.message}" type="video/mp4">
            </video>`;
                }

                const messageHtml = isMe ?
                    `<div class="user-message message">
                       ${messageContent}
                        <img src="${senderImage}"
                            alt="You" onerror="this.onerror=null;this.src='${defaultImage}';">
                    </div>` :
                    `<div class="bot-message message">
                    ${messageContent}
                        <img src="${senderImage}" alt="Bot" onerror="this.onerror=null;this.src='${defaultImage}';">
                    </div>`;
                // const $message = $(messageHtml).hide().appendTo('#chatMessages').fadeIn();
                $(messageHtml).hide().appendTo('#chatMessages').fadeIn('slow', function() {
                    scrollToBottom();

                    if ($('#chatBox').hasClass('show') && unreadMessages > 0) {
                        markMessagesAsRead();
                    }
                });
                // $('#chatMessages').append(messageHtml);
            }

            // Scroll to Bottom
            function scrollToBottom() {
                const chatMessages = document.getElementById('chatMessages');
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Show Media Preview
            function showMediaPreview(file) {
                const previewContainer = $('#mediaPreview');
                const previewImage = $('#previewImage');
                const previewVideo = $('#previewVideo');
                const removeButton = $('#removeMedia');

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.attr('src', e.target.result).show();
                        previewVideo.hide();
                        removeButton.show();
                        previewContainer.show();
                    };
                    reader.readAsDataURL(file);
                } else if (file.type.startsWith('video/')) {
                    previewVideo.attr('src', URL.createObjectURL(file)).show();
                    previewImage.hide();
                    removeButton.show();
                    previewContainer.show();
                }
            }

            // Remove Media
            $('#removeMedia').click(function() {
                selectedFile = null;
                $('#fileInput').val('');
                $('#mediaPreview').hide();
                $('#previewImage').attr('src', '').hide();
                $('#previewVideo').attr('src', '').hide();
                $(this).hide();
            });
        }
    </script>
@endauth
