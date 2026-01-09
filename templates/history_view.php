<?php
$page_title = '历史记录';
$body_class = 'history-page';
$additional_head_content = '
    <script src="' . BASE_URL . '/js/common.js"></script>
    <script src="' . BASE_URL . '/js/history.js"></script>';
require_once dirname(__DIR__) . '/includes/header.php';
?>
    <nav>
        <ul>
            <a href='<?php echo BASE_URL; ?>'><li>
                返回剪切板
                <span></span><span></span><span></span><span></span>
            </li></a>
            <li data-action='clear-all'>
                清空历史记录
                <span></span><span></span><span></span><span></span>
            </li>
        </ul>
    </nav>

    <div id="message-container"></div>

    <main class='history-container'>
        <?php if (empty($records)): ?>
            <div class='no-records'>暂无历史记录</div>
        <?php else: ?>
            <?php foreach ($records as $id => $record): ?>
                <article class='history-item' id='item-<?php echo $id; ?>'>
                    <header class='history-header'>
                        <time class='history-timestamp'><?php echo htmlspecialchars($record['timestamp']); ?></time>
                        <div class='history-actions'>
                            <button class='btn btn-copy' data-action='copy' data-id='<?php echo $id; ?>'>复制</button>
                            <button class='btn btn-delete' data-action='delete' data-id='<?php echo $id; ?>'>删除</button>
                        </div>
                    </header>
                    <div class='history-content' id='content-<?php echo $id; ?>'><?php echo htmlspecialchars($record['content']); ?></div>
                </article>
            <?php endforeach; ?>

        <?php endif; ?>

        <!-- 分页导航容器 -->
        <div class="pagination-container"></div>
    </main>

    <script>
    HistoryApp.init({
        csrfToken: '<?php echo htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8'); ?>',
        pageUrl: window.location.href,
        perPage: <?php echo isset($pagination) ? $pagination['per_page'] : 20; ?>,
        currentPage: <?php echo isset($pagination) ? $pagination['current_page'] : 1; ?>,
        totalPages: <?php echo isset($pagination) ? $pagination['total_pages'] : 1; ?>,
        total: <?php echo isset($pagination) ? $pagination['total'] : 0; ?>,
        hasMore: <?php echo isset($pagination) && $pagination['has_more'] ? 'true' : 'false'; ?>
    });
    </script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
