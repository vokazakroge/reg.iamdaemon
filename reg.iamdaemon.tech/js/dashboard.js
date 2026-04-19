const dropZone = document.getElementById('dropZone'),
    fileInput = document.getElementById('fileInput'),
    statusMsg = document.getElementById('statusMsg');
['dragenter', 'dragover'].forEach(e => dropZone.addEventListener(e, ev => { ev.preventDefault();
    dropZone.classList.add('dragover') }));
['dragleave', 'drop'].forEach(e => dropZone.addEventListener(e, ev => { ev.preventDefault();
    dropZone.classList.remove('dragover') }));
dropZone.addEventListener('drop', e => handleFiles(e.dataTransfer.files));
fileInput.addEventListener('change', e => handleFiles(e.target.files));

async function handleFiles(files) {
    if (!files.length) return;
    statusMsg.textContent = '⏳ загрузка...';
    statusMsg.className = 'status-msg';
    for (const file of files) {
        const fd = new FormData();
        fd.append('file', file);
        try { const res = await fetch('/api/upload.php', { method: 'POST', body: fd }); const d = await res.json(); if (!res.ok || d.error) throw new Error(d.error || 'ошибка');
            statusMsg.textContent = `✅ ${file.name} загружен`;
            statusMsg.className = 'status-msg success';
            setTimeout(() => location.reload(), 800) } catch (err) { statusMsg.textContent = `❌ ${file.name}: ${err.message}`;
            statusMsg.className = 'status-msg error' }
    }
}

document.querySelectorAll('.btn-icon.delete').forEach(btn => {
    btn.addEventListener('click', async() => {
        const f = btn.dataset.file;
        if (!confirm(`Удалить ${f}?`)) return;
        try {
            const res = await fetch('/api/delete.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ file: f }) });
            const txt = await res.text();
            const d = JSON.parse(txt);
            if (!res.ok || d.error) throw new Error(d.error);
            location.reload()
        } catch (err) { alert('Ошибка: ' + err.message) }
    })
});

const modal = document.getElementById('editorModal'),
    title = document.getElementById('editorTitle'),
    close = document.getElementById('closeModal'),
    cancel = document.getElementById('cancelEdit'),
    save = document.getElementById('saveEdit'),
    editorEl = document.getElementById('codeEditor');
let currentFile = '',
    cm;
document.querySelectorAll('.btn-icon.edit').forEach(btn => {
    btn.addEventListener('click', async() => {
        currentFile = btn.dataset.file;
        title.textContent = `редактирование: ${currentFile}`;
        try {
            const res = await fetch(`/api/read.php?file=${encodeURIComponent(currentFile)}`);
            const txt = await res.text();
            const d = JSON.parse(txt);
            if (!res.ok || d.error) throw new Error(d.error);
            if (!cm) cm = CodeMirror.fromTextArea(editorEl, { lineNumbers: true, theme: 'dracula', mode: 'htmlmixed', viewportMargin: Infinity });
            cm.setValue(d.content);
            cm.refresh();
            modal.classList.add('active')
        } catch (err) { alert('Ошибка: ' + err.message) }
    })
});
close.onclick = cancel.onclick = () => modal.classList.remove('active');
save.onclick = async() => { const c = cm.getValue();
    save.textContent = '⏳ сохраняем...'; try { const res = await fetch('/api/save.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ file: currentFile, content: c }) }); const d = await res.json(); if (!res.ok || d.error) throw new Error(d.error);
        modal.classList.remove('active');
        location.reload() } catch (err) { alert('Ошибка: ' + err.message) } finally { save.textContent = '💾 сохранить' } };