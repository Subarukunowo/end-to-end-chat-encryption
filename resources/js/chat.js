document.addEventListener("DOMContentLoaded", () => {
    const chatBox = document.getElementById('chat-box');
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');
    const statusDot = document.getElementById('connection-status');

// Ganti dengan UUID yang kamu salin dari Tinker tadi
const currentConversationId = '01a0aea2-d945-72b9-a570-c467824f6df8'; 
const currentUserId = 1; // Biarkan angka 1 karena ID $user1 (Pengirim) kamu adalah 1

    // --- 1. FUNGSI ENKRIPSI (Simulasi E2EE Frontend) ---
    // Di aplikasi nyata, gunakan Web Crypto API. Server hanya akan melihat karakter acak ini.
    const encryptMessage = (text) => btoa(encodeURIComponent(text)); 
    const decryptMessage = (ciphertext) => decodeURIComponent(atob(ciphertext));

    // --- 2. LISTENER WEBSOCKET (Menerima Pesan Real-time) ---
    // Pastikan Laravel Echo sudah aktif di resources/js/bootstrap.js
    if (window.Echo) {
        // Ubah warna indikator jika berhasil terhubung ke server Reverb/Pusher
        window.Echo.connector.pusher.connection.bind('connected', () => {
            statusDot.classList.replace('bg-red-500', 'bg-green-500');
            statusDot.title = 'Connected';
        });

        // Dengarkan channel khusus untuk percakapan ini
        window.Echo.private(`conversation.${currentConversationId}`)
            .listen('MessageSent', (e) => {
                // Server mengirimkan ciphertext, browser pengguna yang mendekripsinya
                const rawCiphertext = e.message.encrypted_body;
                const plainText = decryptMessage(rawCiphertext);
                
                const isMine = e.message.sender_id === currentUserId;
                appendMessageToUI(plainText, isMine);
            });
    } else {
        console.warn("Laravel Echo belum terinisialisasi. Cek konfigurasi Vite/Reverb.");
    }

    // --- 3. AKSI MENGIRIM PESAN ---
    sendBtn.addEventListener('click', async () => {
        const text = messageInput.value.trim();
        if (!text) return;

        // Render langsung ke layar pengirim (Optimistic UI) agar terasa instan
        appendMessageToUI(text, true);
        messageInput.value = '';

        // Enkripsi teks SEBELUM dikirim ke backend PostgreSQL
        const encryptedBody = encryptMessage(text);

        try {
            // Hit API backend Laravel untuk menyimpan pesan (endpoint harus dibuat di Controller)
            await fetch(`/api/chat/${currentConversationId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    sender_id: currentUserId,
                    encrypted_body: encryptedBody
                })
            });
        } catch (error) {
            console.error('Gagal mengirim pesan ke server:', error);
        }
    });

    // Menangani pengiriman pesan menggunakan tombol Enter
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendBtn.click();
    });

    // --- 4. FUNGSI RENDER HTML ---
    function appendMessageToUI(text, isMine) {
        const bubble = document.createElement('div');
        bubble.className = isMine ? 'flex justify-end' : 'flex';
        
        // Warna hijau ala WhatsApp untuk pengirim, putih untuk penerima
        const innerClass = isMine 
            ? 'bg-[#dcf8c6] text-gray-800 p-3 rounded-lg rounded-tr-none max-w-md shadow-sm'
            : 'bg-white text-gray-800 p-3 rounded-lg rounded-tl-none max-w-md shadow-sm';

        // Format waktu sederhana
        const timeString = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

        bubble.innerHTML = `
            <div class="${innerClass}">
                <p>${text}</p>
                <span class="text-[10px] text-gray-500 mt-1 block text-right">${timeString} ${isMine ? '✓' : ''}</span>
            </div>
        `;
        
        chatBox.appendChild(bubble);
        chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll ke bawah
    }
});