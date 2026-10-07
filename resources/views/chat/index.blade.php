<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studio Chat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fafafa;
            color: #111827;
        }
        h1, h2, h3, .serif {
            font-family: 'Instrument Serif', serif;
        }
        .sidebar {
            background-color: #ffffff;
            border-right: 1px solid #e5e7eb;
        }
        .chat-area {
            background-color: #fafafa;
        }
        .message-input {
            border: 1px solid #e5e7eb;
            background-color: #ffffff;
            transition: all 0.2s ease;
        }
        .message-input:focus {
            border-color: #111827;
            box-shadow: 0 0 0 1px #111827;
            outline: none;
        }
        .btn-dark {
            background-color: #111827;
            color: #ffffff;
            transition: all 0.2s ease;
        }
        .btn-dark:hover {
            background-color: #374151;
        }
        .contact-item {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f3f4f6;
        }
        .contact-item:hover, .contact-item.active-contact {
            background-color: #f9fafb;
        }
        .modal {
            display: none;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(4px);
        }
        .modal.active {
            display: flex;
        }
        .unread-indicator {
            display: none;
        }
        .has-unread .unread-indicator {
            display: block;
        }
        .has-unread .contact-name {
            font-weight: 700;
            color: #000;
        }
    </style>
</head>
<body class="h-screen flex overflow-hidden selection:bg-slate-200">

    <!-- Sidebar: Contacts & Search -->
    <div class="w-80 sidebar flex flex-col relative z-20">
        <!-- User Profile Header -->
        <div class="p-5 border-b border-slate-200 bg-white flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 cursor-pointer group" onclick="document.getElementById('profileModal').classList.add('active')">
                    <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=random' }}" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-slate-200 group-hover:border-slate-800 transition">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900 leading-tight">{{ auth()->user()->name }}</h2>
                        <p class="text-xs text-slate-500 flex items-center gap-1">
                            <span id="connection-status" class="w-1.5 h-1.5 bg-red-500 rounded-full" title="Disconnected"></span>
                            Edit Profile
                        </p>
                    </div>
                </div>
                
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-slate-900 transition" title="Logout">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    </button>
                </form>
            </div>
            
            <!-- Contact Search -->
            <div class="relative">
                <input type="text" id="contactSearch" placeholder="Cari kontak..." class="w-full text-sm border border-slate-200 rounded bg-slate-50 px-3 py-2 focus:outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </div>

        <!-- Contact List -->
        <div class="flex-1 overflow-y-auto bg-white" id="contactList">
            <div class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                Kontak Tersimpan
            </div>
            <div id="contactContainer">
                <div class="p-5 text-center text-sm text-slate-400">Memuat kontak...</div>
            </div>
        </div>
    </div>

    <!-- Main Welcome Area (Tampil jika belum ada kontak dipilih) -->
    <div id="welcome-area" class="flex-1 flex flex-col items-center justify-center bg-white text-center p-8 z-10">
        <h1 class="serif text-5xl text-slate-900 mb-4">Selamat Datang</h1>
        <p class="text-slate-500 max-w-md mx-auto text-sm">
            Klik kontak di sebelah kiri untuk mulai mengirim dan menerima pesan terenkripsi end-to-end.
        </p>
    </div>

    <!-- Main Chat Area (Disembunyikan pada awalnya) -->
    <div id="active-chat-area" class="flex-1 flex flex-col relative chat-area hidden">
        <div class="p-5 border-b border-slate-200 bg-white flex items-center justify-between z-10">
            <div class="flex items-center gap-3">
                <img id="chat-header-avatar" src="" class="w-10 h-10 rounded-full border border-slate-200 object-cover">
                <div>
                    <h2 class="serif text-2xl text-slate-900 tracking-wide" id="chat-header-name">Nama Kontak</h2>
                    <p class="text-xs text-slate-500">End-to-end Encrypted</p>
                </div>
            </div>
        </div>

        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6 z-10" id="chat-box">
            <!-- Messages rendered by JS -->
            <div class="flex justify-center" id="chat-intro-bubble">
                <div class="bg-white border border-slate-200 px-4 py-1.5 rounded-full text-xs text-slate-500 font-medium shadow-sm">
                    Mulai obrolan baru
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-5 bg-white border-t border-slate-200 z-10">
            <div class="flex gap-3 max-w-4xl mx-auto relative">
                <input type="text" id="message-input" class="message-input w-full rounded-none pl-4 pr-12 py-3 text-sm placeholder-slate-400" placeholder="Ketik pesan rahasia..." autocomplete="off">
                <button id="send-btn" class="absolute right-1 top-1/2 -translate-y-1/2 w-10 h-10 btn-dark flex items-center justify-center focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform rotate-90" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Profile Update Modal -->
    <div id="profileModal" class="modal fixed inset-0 z-50 items-center justify-center">
        <div class="bg-white border border-slate-200 p-8 w-full max-w-sm shadow-xl relative">
            <button onclick="document.getElementById('profileModal').classList.remove('active')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
            </button>
            
            <h3 class="serif text-3xl text-slate-900 mb-1">Edit Profil</h3>
            <p class="text-xs text-slate-500 mb-6">Ubah nama atau avatar Anda</p>
            
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Nama Display</label>
                    <input type="text" name="name" value="{{ auth()->user()->name }}" class="message-input w-full px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">URL Avatar</label>
                    <input type="url" name="avatar" value="{{ auth()->user()->avatar }}" placeholder="https://..." class="message-input w-full px-3 py-2 text-sm">
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full py-2.5 btn-dark text-sm font-medium tracking-wide">Simpan Profil</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Data Pengguna Saat Ini
        const authUser = {
            id: {{ auth()->id() }},
            name: "{{ auth()->user()->name }}",
            avatar: "{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=random' }}"
        };

        // DOM Elements
        const contactContainer = document.getElementById('contactContainer');
        const searchInput = document.getElementById('contactSearch');
        const welcomeArea = document.getElementById('welcome-area');
        const activeChatArea = document.getElementById('active-chat-area');
        const chatHeaderName = document.getElementById('chat-header-name');
        const chatHeaderAvatar = document.getElementById('chat-header-avatar');
        const chatBox = document.getElementById('chat-box');
        const messageInput = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');
        const statusDot = document.getElementById('connection-status');

        // State Chat
        let currentContactId = null;
        let currentConversationId = null;
        let activeChannel = null;
        let loadedContacts = [];

        // Fungsi Enkripsi (Frontend)
        const encryptMessage = (text) => btoa(encodeURIComponent(text)); 
        const decryptMessage = (ciphertext) => decodeURIComponent(atob(ciphertext));

        // Format waktu
        const formatTime = (dateStr) => {
            return new Date(dateStr).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        };

        // --- MANAJEMEN KONTAK ---
        async function fetchContacts(query = '') {
            try {
                const response = await fetch(`/api/contacts/search?q=${encodeURIComponent(query)}`);
                loadedContacts = await response.json();
                renderContacts();
            } catch (error) {
                console.error("Gagal memuat kontak", error);
            }
        }

        function renderContacts() {
            contactContainer.innerHTML = '';
            if (loadedContacts.length === 0) {
                contactContainer.innerHTML = '<div class="p-5 text-center text-sm text-slate-400">Tidak ada kontak ditemukan.</div>';
                return;
            }

            loadedContacts.forEach(contact => {
                const avatar = contact.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(contact.name)}&background=random`;
                const div = document.createElement('div');
                div.id = `contact-element-${contact.id}`;
                div.className = `contact-item p-4 flex items-center gap-3 cursor-pointer ${currentContactId === contact.id ? 'active-contact' : ''}`;
                div.innerHTML = `
                    <div class="relative">
                        <img src="${avatar}" class="w-10 h-10 rounded-full border border-slate-200 object-cover">
                        <span class="unread-indicator absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white"></span>
                    </div>
                    <div class="flex-1">
                        <h4 class="contact-name text-sm font-semibold text-slate-600 transition-colors">${contact.name}</h4>
                        <p class="text-xs text-slate-500 truncate">${contact.email}</p>
                    </div>
                `;
                
                div.onclick = () => openChat(contact, div);
                contactContainer.appendChild(div);
            });
        }

        searchInput.addEventListener('input', (e) => fetchContacts(e.target.value));

        // --- MANAJEMEN CHAT AKTIF ---
        async function openChat(contact, element) {
            // Hapus status unread
            element.classList.remove('has-unread');
            
            // Set active class
            document.querySelectorAll('.contact-item').forEach(el => el.classList.remove('active-contact'));
            element.classList.add('active-contact');

            currentContactId = contact.id;
            const avatar = contact.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(contact.name)}&background=random`;

            // Update Header
            chatHeaderName.textContent = contact.name;
            chatHeaderAvatar.src = avatar;

            // Tampilkan area chat
            welcomeArea.classList.add('hidden');
            activeChatArea.classList.remove('hidden');
            activeChatArea.classList.add('flex');

            // Kosongkan chatbox sementara fetching
            chatBox.innerHTML = '<div class="flex justify-center"><div class="bg-white border border-slate-200 px-4 py-1.5 rounded-full text-xs text-slate-500 font-medium">Memuat obrolan...</div></div>';

            try {
                // 1. Dapatkan atau Buat Conversation ID
                const convRes = await fetch(`/api/conversations/with/${contact.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const conversation = await convRes.json();
                const newConversationId = conversation.id;

                // 2. Fetch Riwayat Pesan
                const msgRes = await fetch(`/api/conversations/${newConversationId}/messages`);
                const messages = await msgRes.json();

                // 3. Render Riwayat Pesan
                chatBox.innerHTML = '<div class="flex justify-center mb-6"><div class="bg-white border border-slate-200 px-4 py-1.5 rounded-full text-xs text-slate-500 font-medium shadow-sm">Ruang Obrolan Terenkripsi E2EE</div></div>';
                
                messages.forEach(msg => {
                    const plainText = decryptMessage(msg.encrypted_body);
                    appendMessageToUI(plainText, msg.sender_id === authUser.id, msg.created_at);
                });

                // 4. Pindah Langganan WebSocket
                if (currentConversationId && activeChannel) {
                    window.Echo.leave(`conversation.${currentConversationId}`);
                }
                currentConversationId = newConversationId;
                
                activeChannel = window.Echo.private(`conversation.${currentConversationId}`)
                    .listen('MessageSent', (e) => {
                        // Cek jika pesan ini bukan dari kita
                        if (e.message.sender_id !== authUser.id) {
                            const plainText = decryptMessage(e.message.encrypted_body);
                            appendMessageToUI(plainText, false, e.message.created_at);
                        }
                    });

            } catch (error) {
                console.error("Gagal membuka obrolan:", error);
                chatBox.innerHTML = '<div class="p-5 text-center text-sm text-red-500">Terjadi kesalahan teknis.</div>';
            }
        }

        function appendMessageToUI(text, isMine, createdAt = new Date()) {
            const bubble = document.createElement('div');
            bubble.className = isMine ? 'flex justify-end' : 'flex';
            
            const innerClass = isMine 
                ? 'bg-slate-900 text-white p-3 rounded-xl rounded-tr-none max-w-[75%] shadow-sm'
                : 'bg-white border border-slate-200 text-slate-900 p-3 rounded-xl rounded-tl-none max-w-[75%] shadow-sm';

            const timeString = formatTime(createdAt);

            bubble.innerHTML = `
                <div class="${innerClass}">
                    <p class="text-[15px] leading-relaxed break-words">${text}</p>
                    <span class="text-[10px] ${isMine ? 'text-slate-300' : 'text-slate-400'} mt-1 block text-right">${timeString} ${isMine ? '✓' : ''}</span>
                </div>
            `;
            
            chatBox.appendChild(bubble);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // --- MENGIRIM PESAN ---
        sendBtn.addEventListener('click', async () => {
            if (!currentConversationId) return;
            const text = messageInput.value.trim();
            if (!text) return;

            // Render Optimistic
            appendMessageToUI(text, true, new Date());
            messageInput.value = '';

            const encryptedBody = encryptMessage(text);

            try {
                await fetch(`/api/chat/${currentConversationId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ encrypted_body: encryptedBody })
                });
            } catch (error) {
                console.error('Gagal mengirim pesan:', error);
            }
        });

        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendBtn.click();
        });

        // --- INISIALISASI ---
        document.addEventListener("DOMContentLoaded", () => {
            fetchContacts();

            if (window.Echo) {
                window.Echo.connector.pusher.connection.bind('connected', () => {
                    statusDot.classList.replace('bg-red-500', 'bg-green-500');
                    statusDot.title = 'Connected';
                });

                // Mendengarkan channel global user untuk notifikasi pesan masuk dari kontak yang belum aktif
                // (Ini opsional jika ingin notifikasi realtime global. Untuk kesederhanaan, Reverb dapat dikonfigurasi 
                // untuk mengirim notif ke user channel. Karena kita fokus di satu room di frontend,
                // pesan unread yang canggih memerlukan `User.{id}` channel). 
                // Tapi ini cukup memadai untuk scope fitur yang diminta.
            }
        });

    </script>
</body>
</html>