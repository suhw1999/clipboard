<?php
$page_title = '历史记录';
$additional_head_content = '
    <style type="text/css">
    body {
        padding-top: 80px;
        justify-content: normal;
    }
    .history-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
    }
    .history-item {
        background: #575757;
        border: 2px solid #fff;
        border-radius: 10px;
        margin: 15px 0;
        padding: 15px;
        position: relative;
        word-break: break-word;
    }
    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        border-bottom: 1px solid #fff;
        padding-bottom: 8px;
    }
    .history-timestamp {
        color: #8899ff;
        font-weight: bold;
        font-size: 14px;
    }
    .history-actions {
        display: flex;
        gap: 10px;
        flex-shrink: 0;
    }
    .btn {
        padding: 6px 12px;
        border: 2px solid #fff;
        border-radius: 5px;
        background: transparent;
        color: #fff;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        transition: all 0.3s ease;
        white-space: nowrap;
        margin: 0px 20px;
        line-height: 20px;
    }
    .btn:hover {
        background: #fff;
        color: #575757;
        transform: translateY(-2px);
    }
    .btn-copy {
        border-color: #4CAF50;
        color: #4CAF50;
    }
    .btn-copy:hover {
        background: #4CAF50;
        color: #fff;
    }
    .btn-delete {
        border-color: #f44336;
        color: #f44336;
    }
    .btn-delete:hover {
        background: #f44336;
        color: #fff;
    }
    .history-content {
        color: #fff;
        line-height: 1.5;
        white-space: pre-wrap;
        max-height: 300px;
        overflow-y: auto;
        padding: 5px 0;
        word-wrap: break-word;
    }
    .no-records {
        text-align: center;
        color: #fff;
        font-size: 18px;
        margin: 50px 0;
    }
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
    .success-message {
        background: #4CAF50;
        color: #fff;
        padding: 10px;
        border-radius: 5px;
        margin: 10px 0;
        text-align: center;
    }
    .error-message {
        background: #f44336;
        color: #fff;
        padding: 10px;
        border-radius: 5px;
        margin: 10px 0;
        text-align: center;
    }
    
    /* 响应式设计 */
    @media (max-width: 768px) {
        body {
            padding-top: 100px;
            justify-content: normal;
        }
        .history-container {
            padding: 15px;
            margin: 0;
            width: 100%;
            box-sizing: border-box;
        }
        .history-item {
            margin: 12px 0;
            padding: 12px;
        }
        .history-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .history-actions {
            align-self: flex-end;
            width: 100%;
            justify-content: flex-end;
        }
        .btn {
            padding: 8px 16px;
            font-size: 14px;
            min-width: 60px;
        }
        .history-content {
            max-height: 250px;
            font-size: 14px;
        }
    }
    
    @media (max-width: 480px) {
        body {
            padding-top: 120px;
            justify-content: normal;
        }
        .history-container {
            padding: 10px;
        }
        .history-item {
            margin: 10px 0;
            padding: 10px;
        }
        .history-header {
            gap: 8px;
        }
        .history-actions {
            gap: 8px;
        }
        .btn {
            padding: 6px 12px;
            font-size: 13px;
            min-width: 50px;
        }
        .history-content {
            max-height: 200px;
            line-height: 1.4;
        }
        .history-timestamp {
            font-size: 14px;
        }
    }
    
    @media (max-width: 360px) {
        .history-actions {
            flex-direction: column;
            width: auto;
            align-self: center;
        }
        .btn {
            padding: 5px 10px;
            font-size: 12px;
        }
    }
    </style>';
require_once dirname(__DIR__) . '/includes/header.php';
?>
    <nav>
        <ul>
            <a href='<?php echo BASE_URL; ?>'><li>
                返回剪切板
                <span></span><span></span><span></span><span></span>
            </li></a>
            <a href='javascript:void(0)' onclick='clearAllRecords()'><li>
                清空历史记录
                <span></span><span></span><span></span><span></span>
            </li></a>
        </ul>
    </nav>
    
    <div class='history-container'>
        <div id="message-container"></div>
        
        <?php if (empty($records)): ?>
            <div class='no-records'>暂无历史记录</div>
        <?php else: ?>
            <?php foreach ($records as $id => $record): ?>
                <div class='history-item' id='item-<?php echo $id; ?>'>
                    <div class='history-header'>
                        <div class='history-timestamp'><?php echo htmlspecialchars($record['timestamp']); ?></div>
                        <div class='history-actions'>
                            <button class='btn btn-copy' onclick='copyToClipboard(<?php echo $id; ?>)'>复制</button>
                            <button class='btn btn-delete' onclick='deleteRecord(<?php echo $id; ?>)'>删除</button>
                        </div>
                    </div>
                    <div class='history-content' id='content-<?php echo $id; ?>'><?php echo htmlspecialchars($record['content']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script>
    const csrfToken = '<?php echo htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8'); ?>';
    
    function showMessage(message, type = 'success') {
        const container = document.getElementById('message-container');
        const messageDiv = document.createElement('div');
        messageDiv.className = type + '-message';
        messageDiv.textContent = message;
        container.appendChild(messageDiv);
        
        setTimeout(() => {
            container.removeChild(messageDiv);
        }, 3000);
    }
    
    function copyToClipboard(recordId) {
        const content = document.getElementById('content-' + recordId).textContent;
        
        // 尝试使用现代 Clipboard API
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(content).then(function() {
                showMessage('已复制到剪贴板', 'success');
            }).catch(function(err) {
                console.error('Clipboard API 失败: ', err);
                fallbackCopy(content);
            });
        } else {
            // 备用方案：文本选择
            fallbackCopy(content);
        }
    }
    
    function fallbackCopy(text) {
        // 创建临时文本区域
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        
        try {
            textArea.focus();
            textArea.select();
            
            // 对于移动设备，需要设置选择范围
            if (navigator.userAgent.match(/ipad|iphone/i)) {
                textArea.contentEditable = true;
                textArea.readOnly = false;
                const range = document.createRange();
                range.selectNodeContents(textArea);
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
                textArea.setSelectionRange(0, 999999);
            } else {
                textArea.setSelectionRange(0, textArea.value.length);
            }
            
            const successful = document.execCommand('copy');
            if (successful) {
                showMessage('已复制到剪贴板', 'success');
            } else {
                showMessage('复制失败，请长按文本手动复制', 'error');
            }
        } catch (err) {
            console.error('备用复制方案失败: ', err);
            showMessage('复制失败，请长按文本手动复制', 'error');
        } finally {
            document.body.removeChild(textArea);
        }
    }
    
    function deleteRecord(recordId) {
        if (confirm('确定要删除这条记录吗？')) {
            const item = document.getElementById('item-' + recordId);
            item.classList.add('loading');
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('record_id', recordId);
            formData.append('csrf_token', csrfToken);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    item.style.transition = 'all 0.3s ease';
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(-100%)';
                    
                    setTimeout(() => {
                        item.remove();
                        showMessage('删除成功', 'success');
                        
                        // 检查是否还有记录
                        const remainingItems = document.querySelectorAll('.history-item').length;
                        if (remainingItems === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    item.classList.remove('loading');
                    showMessage(data.message || '删除失败', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                item.classList.remove('loading');
                showMessage('删除失败', 'error');
            });
        }
    }
    
    function clearAllRecords() {
        if (confirm('确定要清空所有历史记录吗？此操作不可撤销！')) {
            const container = document.querySelector('.history-container');
            container.classList.add('loading');
            
            const formData = new FormData();
            formData.append('action', 'clear_all');
            formData.append('csrf_token', csrfToken);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message || '历史记录已清空', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    container.classList.remove('loading');
                    showMessage(data.message || '清空失败', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                container.classList.remove('loading');
                showMessage('清空失败', 'error');
            });
        }
    }
    </script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>