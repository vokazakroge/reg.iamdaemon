document.querySelectorAll('.btn-action.ban').forEach(btn => {
    btn.addEventListener('click', async() => {
        const username = btn.dataset.username;
        const currentStatus = btn.dataset.status;
        const newStatus = currentStatus === 'active' ? 'banned' : 'active';
        const action = currentStatus === 'active' ? 'заблокировать' : 'разблокировать';

        if (!confirm(`${action} пользователя ${username}?`)) return;

        try {
            const res = await fetch('/admin/api/ban.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, status: newStatus })
            });
            const data = await res.json();

            if (!res.ok || data.error) {
                throw new Error(data.error);
            }

            // Обновляем строку в таблице
            const row = btn.closest('tr');
            const statusCell = row.querySelector('td:nth-child(6)');
            const badge = statusCell.querySelector('.badge');
            badge.textContent = newStatus;
            badge.className = `badge ${newStatus === 'active' ? 'success' : 'error'}`;

            btn.textContent = newStatus === 'active' ? '🔒 Ban' : '🔓 Unban';
            btn.dataset.status = newStatus;

        } catch (err) {
            alert('Ошибка: ' + err.message);
        }
    });
});