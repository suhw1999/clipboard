/**
 * Clipboard 历史记录模块
 * 处理复制、删除、分页功能
 */

const HistoryApp = {
    config: {
        csrfToken: '',
        pageUrl: '',
        perPage: 20,
        currentPage: 1,
        totalPages: 1,
        total: 0,
        hasMore: false,
        visiblePages: 5
    },

    init(config) {
        Object.assign(this.config, config);
        MessageSystem.init();

        // 解析URL参数
        this.parseURLParams();

        // 初始化分页UI
        this.updatePaginationUI();

        // 事件委托：分页按钮点击
        this.initPaginationEvents();

        // 事件委托：操作按钮点击
        this.initActionEvents();

        // 监听浏览器前进/后退
        this.initHistoryListener();

        // 如果URL指定了非第一页且与初始页不同，加载该页
        const urlParams = new URLSearchParams(window.location.search);
        const urlPage = parseInt(urlParams.get('page'), 10);
        if (!isNaN(urlPage) && urlPage > 1 && urlPage !== config.currentPage) {
            this.changePage(urlPage);
        }
    },

    /**
     * 初始化操作按钮事件（事件委托）
     */
    initActionEvents() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-action]');
            if (!btn) return;

            const action = btn.dataset.action;
            const id = parseInt(btn.dataset.id, 10);

            switch (action) {
                case 'copy':
                    this.copyToClipboard(id);
                    break;
                case 'delete':
                    this.deleteRecord(id);
                    break;
                case 'clear-all':
                    this.clearAllRecords();
                    break;
            }
        });
    },

    /**
     * 从URL解析初始页码
     */
    parseURLParams() {
        const params = new URLSearchParams(window.location.search);
        const page = parseInt(params.get('page'), 10);

        if (!isNaN(page) && page > 0) {
            this.config.currentPage = page;
        }
    },

    /**
     * 初始化分页事件（事件委托）
     */
    initPaginationEvents() {
        const container = document.querySelector('.history-container');
        if (!container) return;

        container.addEventListener('click', (e) => {
            const btn = e.target.closest('.pagination-btn');
            if (btn && !btn.disabled && !btn.classList.contains('active')) {
                const page = parseInt(btn.dataset.page, 10);
                if (!isNaN(page)) {
                    this.changePage(page);
                }
            }
        });
    },

    /**
     * 初始化浏览器历史监听（前进/后退按钮）
     */
    initHistoryListener() {
        window.addEventListener('popstate', (event) => {
            if (event.state && event.state.page) {
                this.changePageWithoutPush(event.state.page);
            } else {
                const urlParams = new URLSearchParams(window.location.search);
                const page = parseInt(urlParams.get('page')) || 1;
                this.changePageWithoutPush(page);
            }
        });
    },

    /**
     * 切换到指定页码
     * @param {number} page - 目标页码
     */
    async changePage(page) {
        await this._loadPage(page, true);
    },

    /**
     * 切换页码但不更新URL（用于popstate）
     * @param {number} page - 目标页码
     */
    async changePageWithoutPush(page) {
        await this._loadPage(page, false);
    },

    /**
     * 加载指定页面
     * @param {number} page - 目标页码
     * @param {boolean} updateHistory - 是否更新浏览器历史
     */
    async _loadPage(page, updateHistory = true) {
        // 参数校验
        const targetPage = Math.max(1, Math.min(page, this.config.totalPages || 1));

        // 避免重复请求同一页
        if (targetPage === this.config.currentPage && this.config.totalPages > 0) {
            return;
        }

        // 显示加载状态
        const container = document.querySelector('.history-container');
        if (container) {
            container.classList.add('loading');
        }

        try {
            const url = `api/get_records.php?page=${targetPage}&per_page=${this.config.perPage}`;
            const data = await API.get(url);

            if (data.success) {
                // 更新配置状态
                this.config.currentPage = data.pagination.current_page;
                this.config.perPage = data.pagination.per_page;
                this.config.totalPages = data.pagination.total_pages;
                this.config.total = data.pagination.total;
                this.config.hasMore = data.pagination.has_more;

                // 清空并重新渲染记录
                this.replaceRecords(data.records);

                // 更新分页UI
                this.updatePaginationUI();

                // 更新浏览器URL（History API）
                if (updateHistory) {
                    this.updateURL();
                }

                // 滚动到顶部
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                MessageSystem.error(data.message || '加载失败');
            }
        } catch (error) {
            console.error('Pagination error:', error);
            MessageSystem.error(error.message || '加载失败');
        } finally {
            if (container) {
                container.classList.remove('loading');
            }
        }
    },

    /**
     * 替换容器内所有记录（非追加）
     * @param {Array} records - 记录数组
     */
    replaceRecords(records) {
        const container = document.querySelector('.history-container');
        if (!container) return;

        // 清空现有记录（保留分页区域）
        const existingItems = container.querySelectorAll('.history-item');
        existingItems.forEach(item => item.remove());

        // 处理无记录情况
        const noRecordsEl = container.querySelector('.no-records');
        if (noRecordsEl) noRecordsEl.remove();

        if (records.length === 0) {
            const emptyDiv = document.createElement('div');
            emptyDiv.className = 'no-records';
            emptyDiv.textContent = '暂无历史记录';
            // 插入到分页区域之前
            const paginationEl = container.querySelector('.pagination-container');
            if (paginationEl) {
                container.insertBefore(emptyDiv, paginationEl);
            } else {
                container.appendChild(emptyDiv);
            }
            return;
        }

        // 渲染新记录（插入到分页区域之前）
        const paginationEl = container.querySelector('.pagination-container');
        records.forEach(record => {
            const item = document.createElement('article');
            item.className = 'history-item';
            item.id = 'item-' + record.id;
            item.innerHTML = `
                <header class='history-header'>
                    <time class='history-timestamp'>${this.escapeHtml(record.timestamp)}</time>
                    <div class='history-actions'>
                        <button class='btn btn-copy' data-action='copy' data-id='${record.id}'>复制</button>
                        <button class='btn btn-delete' data-action='delete' data-id='${record.id}'>删除</button>
                    </div>
                </header>
                <div class='history-content' id='content-${record.id}'>${this.escapeHtml(record.content)}</div>
            `;
            if (paginationEl) {
                container.insertBefore(item, paginationEl);
            } else {
                container.appendChild(item);
            }
        });
    },

    /**
     * 更新分页导航UI
     * 生成：首页 | 上一页 | ... | 页码 | ... | 下一页 | 尾页
     */
    updatePaginationUI() {
        const paginationContainer = document.querySelector('.pagination-container');
        if (!paginationContainer) return;

        const { currentPage, totalPages, total, perPage } = this.config;

        // 无数据或单页时隐藏分页
        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            paginationContainer.style.display = 'none';
            return;
        }
        paginationContainer.style.display = 'flex';

        let html = '';

        // 首页和上一页
        html += `<button class="pagination-btn" data-page="1" ${currentPage === 1 ? 'disabled' : ''}>首页</button>`;
        html += `<button class="pagination-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>上一页</button>`;

        // 计算显示的页码范围
        const visiblePages = this.config.visiblePages;
        const halfVisible = Math.floor(visiblePages / 2);
        let startPage = Math.max(1, currentPage - halfVisible);
        let endPage = Math.min(totalPages, currentPage + halfVisible);

        // 调整边界情况
        if (currentPage <= halfVisible) {
            endPage = Math.min(totalPages, visiblePages);
        }
        if (currentPage > totalPages - halfVisible) {
            startPage = Math.max(1, totalPages - visiblePages + 1);
        }

        // 显示前省略号
        if (startPage > 1) {
            html += `<button class="pagination-btn" data-page="1">1</button>`;
            if (startPage > 2) {
                html += `<span class="pagination-ellipsis">...</span>`;
            }
        }

        // 页码按钮
        for (let i = startPage; i <= endPage; i++) {
            if (startPage > 1 && i === 1) continue; // 避免重复显示第1页
            const isActive = i === currentPage ? 'active' : '';
            html += `<button class="pagination-btn ${isActive}" data-page="${i}">${i}</button>`;
        }

        // 显示后省略号
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<span class="pagination-ellipsis">...</span>`;
            }
            html += `<button class="pagination-btn" data-page="${totalPages}">${totalPages}</button>`;
        }

        // 下一页和尾页
        html += `<button class="pagination-btn" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>下一页</button>`;
        html += `<button class="pagination-btn" data-page="${totalPages}" ${currentPage === totalPages ? 'disabled' : ''}>尾页</button>`;

        paginationContainer.innerHTML = html;
    },

    /**
     * 更新浏览器URL（不刷新页面）
     */
    updateURL() {
        const url = new URL(window.location.href);
        url.searchParams.set('page', this.config.currentPage);

        history.pushState(
            { page: this.config.currentPage },
            '',
            url.toString()
        );
    },

    // 复制到剪贴板
    async copyToClipboard(recordId) {
        const contentElement = document.getElementById('content-' + recordId);
        if (!contentElement) {
            MessageSystem.error('记录不存在');
            return;
        }

        const content = contentElement.textContent;
        const success = await ClipboardUtil.copy(content);

        if (success) {
            MessageSystem.success('已复制到剪贴板');
        } else {
            MessageSystem.error('复制失败，请长按文本手动复制');
        }
    },

    // 删除单条记录
    async deleteRecord(recordId) {
        if (!confirm('确定要删除这条记录吗？')) {
            return;
        }

        const item = document.getElementById('item-' + recordId);
        if (!item) return;

        item.classList.add('loading');

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('record_id', recordId);
        formData.append('csrf_token', this.config.csrfToken);

        try {
            const data = await API.post(this.config.pageUrl || window.location.href, formData);

            if (data.success) {
                // 动画移除
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '0';
                item.style.transform = 'translateX(-100%)';

                setTimeout(() => {
                    item.remove();
                    MessageSystem.success('删除成功');

                    // 更新总数
                    this.config.total--;
                    this.config.totalPages = Math.ceil(this.config.total / this.config.perPage);

                    // 检查当前页是否还有记录
                    const remainingItems = document.querySelectorAll('.history-item').length;
                    if (remainingItems === 0) {
                        // 如果当前页没有记录了，回到上一页或第一页
                        if (this.config.currentPage > 1) {
                            this.changePage(this.config.currentPage - 1);
                        } else {
                            location.reload();
                        }
                    } else {
                        // 更新分页UI
                        this.updatePaginationUI();
                    }
                }, 300);
            } else {
                item.classList.remove('loading');
                MessageSystem.error(data.message || '删除失败');
            }
        } catch (error) {
            console.error('Error:', error);
            item.classList.remove('loading');
            MessageSystem.error(error.message || '删除失败');
        }
    },

    // 清空所有记录
    async clearAllRecords() {
        if (!confirm('确定要清空所有历史记录吗？此操作不可撤销！')) {
            return;
        }

        const container = document.querySelector('.history-container');
        if (!container) return;

        container.classList.add('loading');

        const formData = new FormData();
        formData.append('action', 'clear_all');
        formData.append('csrf_token', this.config.csrfToken);

        try {
            const data = await API.post(this.config.pageUrl || window.location.href, formData);

            if (data.success) {
                MessageSystem.success(data.message || '历史记录已清空');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                container.classList.remove('loading');
                MessageSystem.error(data.message || '清空失败');
            }
        } catch (error) {
            console.error('Error:', error);
            container.classList.remove('loading');
            MessageSystem.error(error.message || '清空失败');
        }
    },

    // HTML转义
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// 导出到全局
window.HistoryApp = HistoryApp;

// 全局函数（兼容旧代码）
window.copyToClipboard = (recordId) => HistoryApp.copyToClipboard(recordId);
window.deleteRecord = (recordId) => HistoryApp.deleteRecord(recordId);
window.clearAllRecords = () => HistoryApp.clearAllRecords();
