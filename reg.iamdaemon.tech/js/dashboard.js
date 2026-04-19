// SIDEBAR TOGGLE
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const menuBtn = document.getElementById('menuBtn');

// Desktop toggle
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        // Сохраняем состояние
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });

    // Восстанавливаем состояние
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }
}

// Mobile menu
if (menuBtn) {
    menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-open');
    });
}

// NAVIGATION
document.querySelectorAll('.nav-item[data-section]').forEach(item => {
    item.addEventListener('click', function(e) {
        // Если это внешняя ссылка - не предотвращаем
        if (this.href && this.href.includes('reg.iamdaemon.tech')) {
            return;
        }

        e.preventDefault();

        // Убираем active со всех
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));

        // Добавляем active текущему
        this.classList.add('active');
        const sectionId = this.dataset.section;
        const section = document.getElementById(`section-${sectionId}`);
        if (section) {
            section.classList.add('active');
        }
    });
});

// FILE UPLOAD
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const statusMsg = document.getElementById('statusMsg');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    dropZone.classList.add('dragover');
}

function unhighlight(e) {
    dropZone.classList.remove('dragover');
}

dropZone.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
}

fileInput.addEventListener('change', function() {
    handleFiles(this.files);
});

function handleFiles(files) {
    if (files.length === 0) return;

    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }

    statusMsg.className = 'status-msg';
    statusMsg.textContent = 'Загрузка...';
    statusMsg.style.display = 'block';

    fetch('/api/upload.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                statusMsg.className = 'status-msg error';
                statusMsg.textContent = 'Ошибка: ' + data.error;
            } else {
                statusMsg.className = 'status-msg success';
                statusMsg.textContent = '✅ Загружено файлов: ' + data.count;
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(e => {
            statusMsg.className = 'status-msg error';
            statusMsg.textContent = 'Ошибка сети: ' + e.message;
        });
}

// FILE DELETE
document.querySelectorAll('.btn-icon.delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const filename = this.dataset.file;
        if (!confirm(`Удалить ${filename}?`)) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('file', filename);

        fetch('/api/files.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Ошибка: ' + data.error);
                }
            });
    });
});

// FILE EDIT (CodeMirror)
let editor;
const editorModal = document.getElementById('editorModal');
const editorTitle = document.getElementById('editorTitle');
const closeModal = document.getElementById('closeModal');
const cancelEdit = document.getElementById('cancelEdit');
const saveEdit = document.getElementById('saveEdit');

document.querySelectorAll('.btn-icon.edit').forEach(btn => {
    btn.addEventListener('click', function() {
        const filename = this.dataset.file;
        openEditor(filename);
    });
});

function openEditor(filename) {
    editorTitle.textContent = 'Editing: ' + filename;
    editorModal.classList.add('active');

    // Загружаем содержимое
    fetch(`/api/files.php?action=edit&file=${encodeURIComponent(filename)}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                alert('Ошибка: ' + data.error);
                return;
            }

            const ext = filename.split('.').pop().toLowerCase();
            const mode = getModeFromExt(ext);

            // Инициализируем CodeMirror если ещё нет
            if (!editor) {
                editor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), {
                    mode: mode,
                    theme: 'dracula',
                    lineNumbers: true,
                    autoCloseTags: true,
                    matchBrackets: true
                });
            } else {
                editor.setOption('mode', mode);
            }

            editor.setValue(data.content);
            editor.refresh();
        });
}

function getModeFromExt(ext) {
    const modes = {
        'html': 'htmlmixed',
        'htm': 'htmlmixed',
        'css': 'css',
        'js': 'javascript',
        'json': 'javascript',
        'php': 'php',
        'xml': 'xml',
        'svg': 'xml'
    };
    return modes[ext] || 'text';
}

closeModal.addEventListener('click', () => {
    editorModal.classList.remove('active');
});

cancelEdit.addEventListener('click', () => {
    editorModal.classList.remove('active');
});

saveEdit.addEventListener('click', () => {
    if (!editor) return;

    const filename = editorTitle.textContent.replace('Editing: ', '');
    const content = editor.getValue();

    const formData = new FormData();
    formData.append('action', 'save');
    formData.append('file', filename);
    formData.append('content', content);

    saveEdit.disabled = true;
    saveEdit.textContent = 'Сохранение...';

    fetch('/api/files.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                editorModal.classList.remove('active');
                setTimeout(() => location.reload(), 500);
            } else {
                alert('Ошибка: ' + data.error);
                saveEdit.disabled = false;
                saveEdit.textContent = 'Save';
            }
        })
        .catch(e => {
            alert('Ошибка сети: ' + e.message);
            saveEdit.disabled = false;
            saveEdit.textContent = 'Save';
        });
});

// Закрытие модалки по клику вне
editorModal.addEventListener('click', (e) => {
    if (e.target === editorModal) {
        editorModal.classList.remove('active');
    }
});