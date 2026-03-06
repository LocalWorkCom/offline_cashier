@extends('website.layouts.master')

@section('styles')
    <style>
        .message-content {
            padding: 8px 12px;
            background: #f1f1f1;
            border-radius: 4px;
            margin-top: 4px;
            display: inline-block;
            max-width: 80%;
        }

        /* Preview styling */
        #mediaPreview {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-top: 5px;
            display: none;
        }

        #previewImage,
        #previewVideo {
            max-height: 150px;
            max-width: 100%;
        }
    </style>
@endsection

@section('content')


    <section class="inner-header pt-5 mt-5">
        <div class="container pt-sm-5 pt-4 mt-3">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('auth.home')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('chat.customer_service')</li>
                </ol>
            </nav>
        </div>
    </section>
    <section>
        <div class="container py-3">
            <div class="card mt-2 overflow-hidden">
                <div class="card-header bg-white">
                    <div class="chat-header">
                        <div class="d-flex align-items-center">
                            <div class="user-img">
                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}" alt="User">
                            </div>
                            <div class="user-content mx-2">
                                <h6 class="text-muted fw-bold"> @lang('chat.customer_service') </h6>
                                <small class="text-success">@lang('chat.online') </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body" id="chat_area" style="height: 400px; overflow-y: auto;">
                    <div class="chat d-flex justify-content-between align-items-center mb-4">
                        <div class="message-part w-100">
                            <small class="text-muted">
                                {{ $receiver->first_name }} | @lang('chat.customer_service')
                            </small>

                                <div class="message-content">@lang('chat.howcanhelp')</div>
                        </div>
                        <div class="img-part mx-2">
                            <img src="{{ $receiver->image ?? asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}"
                                alt="" height="40" width="40" style="border-radius: 50%;" />
                        </div>
                    </div>
                    @if ($messages)
                        @foreach ($messages as $message)
                            @if ($message->sender != auth('client')->user()->id)
                                <div class="chat d-flex justify-content-between align-items-center mb-4">
                                    <div class="message-part w-100">
                                        <small class="text-muted">
                                            {{ $receiver->first_name }} | @lang('chat.customer_service')
                                        </small>
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
                                            <div class="message-content">{{ $message->message }}</div>
                                        @endif
                                    </div>
                                    <div class="img-part mx-2">
                                        <img src="{{ $receiver->image ?? asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}"
                                            alt="" height="40" width="40" style="border-radius: 50%;" />
                                    </div>
                                </div>
                            @else
                                <div class="chat d-flex justify-content-between align-items-center mb-4">
                                    <div class="img-part mx-2">
                                        <img src="{{ $sender->image ?? asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}"
                                            alt="" height="40" width="40" style="border-radius: 50%;" />
                                    </div>
                                    <div class="message-part w-100">
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
                                            <div class="message-content">{{ $message->message }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
                <div class="card-footer">
                    <div class="chat-input border-0 pt-4 d-flex align-items-center">
                        <!-- Hidden file input -->
                        <input type="file" id="fileInput" accept="image/*,video/*" style="display: none;">

                        <!-- File attachment button -->
                        <a type="button" class="mx-1" id="attachFile">
                            <img class="chat-icon"
                                src="{{ asset('front/AlKout-Resturant/SiteAssets/images/paperclip.svg') }}"
                                alt="@lang(' chat.attach_file')" />
                        </a>

                        <!-- Emoji button and picker container -->
                        <div class="emoji-picker-container">
                            <a type="button" class="mx-1 emoji-btn" id="emojiBtn">
                                <img class="chat-icon"
                                    src="{{ asset('front/AlKout-Resturant/SiteAssets/images/emoji.svg') }}"
                                    alt="@lang('chat.emoji')" />
                            </a>
                            <emoji-picker id="emojiPicker" style="display: none;"></emoji-picker>
                        </div>

                        <!-- Send button -->
                        <a type="button" class="mx-1" id="send">
                            <img class="chat-icon" src="{{ asset('front/AlKout-Resturant/SiteAssets/images/send.svg') }}"
                                alt="@lang('chat.send')" />
                        </a>

                        <!-- Text input -->
                        <input type="text" id="chatInput" class="form-control" placeholder="@lang('chat.type_message')">
                    </div>
                    <!-- Preview container -->
                    <!-- In your preview container section -->
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
            </div>
        </div>
    </section>
    {{-- <section class="before-footer"></section> --}}

@endsection

@push('scripts')
    <script>
        // State Management
        let selectedFile = null;
        // const picker = document.querySelector('emoji-picker');

        // Toggle emoji picker visibility
        $('#emojiBtn').click(function(e) {
            e.stopPropagation();
            $('#emojiPicker').toggle();

        });
        // DOM Ready
        $(document).ready(function() {
            scrollToBottom();
            // Handle emoji selection
            picker.addEventListener('emoji-click', event => {
                const currentInput = $('#chatInput').val();
                $('#chatInput').val(currentInput + event.detail.unicode);
                $('#emojiPicker').hide();
            });
            // File Attachment Handling
            $('#attachFile').click(() => $('#fileInput').click());

            $('#fileInput').change(function(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/quicktime'];
                if (!validTypes.includes(file.type)) {
                    alert('Only images (JPEG, PNG, GIF) and videos (MP4, MOV) are allowed');
                    return;
                }

                // Validate file size (10MB max)
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size should be less than 10MB');
                    return;
                }

                console.log(file);

                selectedFile = file;
                showMediaPreview(file);
            });


            // Send Message (click or enter key)
            $('#send').click(sendMessage);
            $('#chatInput').keypress(function(e) {
                if (e.which == 13) sendMessage();
            });

            // Pusher Message Handler
            channel.bind('chatMessage', function(data) {
                
                renderMessage(data, data.sender.id == userId);
                scrollToBottom();
            });
        });



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
            console.log(formData.media);

            // Optimistic update for text messages
            if (message && !selectedFile) {
                const senderMessage = `
                    <div class="chat d-flex justify-content-between align-items-center mb-4">
                        <div class="img-part mx-2">
                            <img src="{{ auth('client')->user()->image ?? asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}" alt="" height="40" width="40" style="border-radius: 50%;"/>
                        </div>
                        <div class="message-part w-100">
                            <div class="message-content">${message}</div>
                        </div>
                    </div>`;
                // $('#chat_area').append(senderMessage);
                $('#chatInput').val('');
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
                    scrollToBottom();
                },
                error: function(xhr) {
                    alert('Failed to send message: ' + (xhr.responseJSON?.message || 'Server error'));
                }
            });
        }

        // Render Incoming Message
        function renderMessage(data, isMe) {
            let messageContent;

            if (data.type === 'text') {
                messageContent = `<div class="message-content">${data.message}</div>`;
            } else if (data.type === 'image') {
                messageContent = `<img src="${data.message}" alt="image" style="max-width: 200px; max-height: 200px;">`;
            } else {
                messageContent = `
            <video controls style="max-width: 200px; max-height: 200px;">
                <source src="${data.message}" type="video/mp4">
                Your browser doesn't support video
            </video>`;
            }

            const defaultImage = "{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}";
            const senderImage = data.sender.image ? data.sender.image : defaultImage;

            const imageHtml =
                `<img src="${senderImage}" alt="" height="40" width="40" style="border-radius: 50%;" onerror="this.onerror=null;this.src='${defaultImage}';" />`;

            const messageHtml = `
        <div class="chat d-flex justify-content-between align-items-center mb-4">
            ${isMe ? `
                        <div class="img-part mx-2">
                            ${imageHtml}
                        </div>
                        <div class="message-part w-100">
                            ${messageContent}
                        </div>
                    ` : `
                        <div class="message-part w-100">
                            <small class="text-muted">
                                ${data.sender.first_name} | @lang('chat.customer_service')
                            </small>
                            ${messageContent}
                        </div>
                        <div class="img-part mx-2">
                            ${imageHtml}
                        </div>
                    `}
        </div>`;

            $('#chat_area').append(messageHtml);
        }

        // Scroll to Bottom
        function scrollToBottom() {
            const chatArea = document.getElementById('chat_area');
            chatArea.scrollTop = chatArea.scrollHeight;
        }
        // Update the showMediaPreview function
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
                    removeButton.show(); // Show remove button when image is selected
                    previewContainer.show();
                };
                reader.readAsDataURL(file);
            } else if (file.type.startsWith('video/')) {
                previewVideo.attr('src', URL.createObjectURL(file)).show();
                previewImage.hide();
                removeButton.show(); // Show remove button when video is selected
                previewContainer.show();
            }
        }

        // Update the removeMedia handler
        $('#removeMedia').click(function() {
            selectedFile = null;
            $('#fileInput').val('');
            $('#mediaPreview').hide();
            $('#previewImage').attr('src', '').hide();
            $('#previewVideo').attr('src', '').hide();
            $(this).hide(); // Hide the remove button again
        });
    </script>
@endpush
