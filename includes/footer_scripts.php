<script>
(function() {
    const btnDarkMode = document.getElementById('btn-darkmode');
    const htmlElement = document.documentElement;
    if (!btnDarkMode) return;
    const iconDarkMode = btnDarkMode.querySelector('i');
    const currentTheme = localStorage.getItem('theme') || 'light';
    setTheme(currentTheme);
    btnDarkMode.addEventListener('click', () => {
        setTheme(htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light');
    });
    function setTheme(theme) {
        htmlElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);
        iconDarkMode.className = theme === 'dark' ? 'fa-solid fa-sun text-warning' : 'fa-solid fa-moon';
    }

    const bellBtn = document.getElementById('bell-btn');
    if (bellBtn) {
        bellBtn.addEventListener('click', function() {
            fetch('api_read_notif.php', { method: 'POST' });
            const badge = document.getElementById('notif-badge');
            if (badge) badge.classList.add('d-none');
            document.querySelectorAll('.notif-item.unread-bg').forEach(el => el.classList.remove('unread-bg'));
        });
    }

    let lastNotifCount = <?php echo isset($unread_notif_count) ? (int)$unread_notif_count : 0; ?>;
    let lastNotifId = 0;

    async function pollNotifications() {
        try {
            const res = await fetch('api_notif_poll.php');
            const data = await res.json();
            if (data.status !== 'success') return;

            const badge = document.getElementById('notif-badge');
            if (badge) {
                if (data.unread_count > 0) {
                    badge.classList.remove('d-none');
                    badge.textContent = data.unread_count;
                } else {
                    badge.classList.add('d-none');
                }
            }

            if (data.latest && data.latest.id > lastNotifId && lastNotifId > 0) {
                showToast(data.latest);
            }
            if (data.latest) lastNotifId = data.latest.id;
            lastNotifCount = data.unread_count;
        } catch (e) {}
    }

    function showToast(notif) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'alert alert-light shadow-lg border-0 d-flex align-items-center fade show';
        toast.style.borderRadius = '12px';
        toast.innerHTML = `
            <i class="fa-solid fa-bell text-primary me-2 fs-5"></i>
            <div><strong>${notif.sender_name}</strong><br><small>${notif.message}</small></div>
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.remove()"></button>
        `;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }

    pollNotifications();
    setInterval(pollNotifications, 30000);
})();
</script>
