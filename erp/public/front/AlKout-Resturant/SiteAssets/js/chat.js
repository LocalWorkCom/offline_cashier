// resources/js/chat.js
class ChatManager {
    constructor(channelId, userId, receiverId) {
        this.channelId = channelId;
        this.userId = userId;
        this.receiverId = receiverId;
        this.selectedFile = null;
        this.initialize();
    }

    initialize() {
        this.setupPusher();
        this.setupEventListeners();
        this.scrollToBottom();
    }

    setupPusher() {
        this.pusher = new Pusher("{{ config('broadcasting.connections.pusher.key') }}", {
            cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
            forceTLS: true
        });

        this.channel = this.pusher.subscribe('chat-' + this.channelId);
        this.channel.bind('chatMessage', (data) => {
            this.renderMessage(data, data.sender.id == this.userId);
            this.showNewMessageNotification();
            this.scrollToBottom();
        });
    }

    setupEventListeners() {
        // File attachment
        $('#attachFile').click(() => $('#fileInput').click());

        $('#fileInput').change((e) => this.handleFileSelect(e));
        $('#removeMedia').click(() => this.removeMedia());
        $('#send').click(() => this.sendMessage());
        $('#chatInput').keypress((e) => {
            if (e.which == 13) this.sendMessage();
        });
    }

    handleFileSelect(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file type and size
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/quicktime'];
        if (!validTypes.includes(file.type)) {
            alert('Only images (JPEG, PNG, GIF) and videos (MP4, MOV) are allowed');
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('File size should be less than 10MB');
            return;
        }

        this.selectedFile = file;
        this.showMediaPreview(file);
    }

    showMediaPreview(file) {
        const previewContainer = $('#mediaPreview');
        const previewImage = $('#previewImage');
        const previewVideo = $('#previewVideo');
        const removeButton = $('#removeMedia');

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
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

    removeMedia() {
        this.selectedFile = null;
        $('#fileInput').val('');
        $('#mediaPreview').hide();
        $('#previewImage').attr('src', '').hide();
        $('#previewVideo').attr('src', '').hide();
        $('#removeMedia').hide();
    }

    sendMessage() {
        const message = $('#chatInput').val().trim();

        if (!message && !this.selectedFile) {
            alert('Please enter a message or attach a file');
            return;
        }

        const formData = new FormData();
        formData.append('_token', "{{ csrf_token() }}");
        if (message) formData.append('message', message);
        if (this.selectedFile) formData.append('media', this.selectedFile);
        formData.append('sender_guard', 'client');
        formData.append('guard_type', 'employee');
        formData.append('channel_id', this.channelId);
        formData.append('user_id', this.receiverId);
        formData.append('type', 'web');

        // Optimistic update for text messages
        if (message && !this.selectedFile) {
            this.addMessageToUI(message, true);
            $('#chatInput').val('');
        }

        $.ajax({
            url: "{{ route('chat.sender') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                if (this.selectedFile) {
                    this.removeMedia();
                    $('#chatInput').val('');
                }
                this.scrollToBottom();
            },
            error: (xhr) => {
                alert('Failed to send message: ' + (xhr.responseJSON?.message || 'Server error'));
            }
        });
    }

    renderMessage(data, isMe) {
        let messageContent;

        if (data.type === 'text') {
            messageContent = `<div class="message-content">${data.message}</div>`;
        } else if (data.type === 'image') {
            messageContent = `<img src="${data.message}" alt="image" style="max-width: 200px; max-height: 200px;">`;
        } else {
            messageContent = `
                <video controls style="max-width: 200px; max-height: 200px;">
                    <source src="${data.message}" type="${data.mime}">
                    Your browser doesn't support video
                </video>`;
        }

        const messageHtml = this.getMessageHtml(data, isMe, messageContent);
        $('#chatMessages').append(messageHtml);
    }

    getMessageHtml(data, isMe, content) {
        return `
            <div class="${isMe ? 'user-message' : 'bot-message'} message">
                <img src="${isMe ? data.sender.image : data.receiver.image}" alt="${isMe ? 'You' : 'Bot'}">
                ${content}
            </div>`;
    }

    showNewMessageNotification() {
        if (!$('#chatBox').hasClass('show')) {
            $('#chatButton').addClass('has-new-message');
            $('.default-text').text('New Message!');
        }
    }

    scrollToBottom() {
        const chatArea = document.getElementById('chatMessages');
        chatArea.scrollTop = chatArea.scrollHeight;
    }
}

// Initialize when document is ready
$(document).ready(function() {
    // Check if the chat elements exist on the page
    if ($('#chatButton').length > 0 && $('#chatBox').length > 0) {
        // Initialize chat manager with data from Blade
        const chatManager = new ChatManager(
            "{{ $chat_channel->id ?? '' }}",
            "{{ auth('client')->user()->id ?? '' }}",
            "{{ $chat_receiver->id ?? '' }}"
        );

        // Chat toggle functions
        function toggleChat() {
            const chatBox = $('#chatBox');
            if (chatBox.hasClass('show')) {
                minimizeChat();
            } else {
                chatBox.addClass('show');
                $('#chatButton').removeClass('has-new-message');
                $('.default-text').text("@lang('chat.startchat')");
                chatManager.scrollToBottom();
            }
        }

        function minimizeChat() {
            $('#chatBox').removeClass('show');
            $('.default-icon').addClass('d-none');
            $('.default-text').addClass('d-none');
            $('.chat-close-btn').removeClass('d-none');
            $('.user-icon').removeClass('d-none');
        }

        function closeChat() {
            $('#chatBox').removeClass('show');
            $('.default-icon').removeClass('d-none');
            $('.default-text').removeClass('d-none');
            $('.chat-close-btn').addClass('d-none');
            $('.user-icon').addClass('d-none');
        }

        // Expose functions to global scope
        window.toggleChat = toggleChat;
        window.minimizeChat = minimizeChat;
        window.closeChat = closeChat;
    }
});
