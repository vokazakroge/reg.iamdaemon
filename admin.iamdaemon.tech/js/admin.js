window.banUser = function(username, status) {
    console.log('🔒 banUser:', username, status);
    var newStatus = status === 'active' ? 'banned' : 'active';

    // Убрали confirm
    console.log('📤 Отправка...');

    fetch('/api/ban.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                status: newStatus
            })
        })
        .then(function(r) {
            return r.json();
        })
        .then(function(data) {
            if (data.error) {
                alert('Ошибка: ' + data.error);
            } else {
                location.reload();
            }
        })
        .catch(function(e) {
            alert('Ошибка: ' + e.message);
        });
};