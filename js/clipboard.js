/**
 * Clipboard 主页模块
 * 处理内容提交功能
 */

const ClipboardApp = {
    config: {
        isAuthenticated: false,
        csrfToken: '',
        maxContentLength: 10000,
        baseUrl: '/clipboard',
        submitUrl: 'api/submit.php'
    },

    init(config) {
        Object.assign(this.config, config);
        this.bindEvents();
    },

    bindEvents() {
        document.addEventListener('DOMContentLoaded', () => {
            const contentInput = document.getElementById('content');
            if (!contentInput) return;

            // 自动聚焦到输入框
            contentInput.focus();

            // Shift+Enter 快捷提交
            contentInput.addEventListener('keydown', (event) => {
                if (event.shiftKey && event.key === 'Enter') {
                    event.preventDefault();
                    this.submitContent();
                }
            });

            // 事件委托：处理 data-action 按钮点击
            document.addEventListener('click', (e) => {
                const actionElement = e.target.closest('[data-action]');
                if (actionElement) {
                    const action = actionElement.dataset.action;
                    if (action === 'submit') {
                        this.submitContent();
                    }
                }
            });
        });
    },

    async submitContent() {
        const content = document.getElementById('content').value;

        // 验证内容
        if (!content.trim()) {
            alert('内容不能为空！');
            return;
        }

        if (content.length > this.config.maxContentLength) {
            alert(`内容长度超出限制（最大${this.config.maxContentLength}字符）！`);
            return;
        }

        const formData = new FormData();
        formData.append('copy', content);
        formData.append('csrf_token', this.config.csrfToken);

        // 如果未认证，需要输入密码
        if (!this.config.isAuthenticated) {
            const password = prompt('请输入密码：');
            if (!password) {
                alert('验证失败。');
                return;
            }
            formData.append('password', password);
        }

        try {
            const data = await API.post(this.config.submitUrl, formData);

            if (data.success) {
                window.location.href = this.config.baseUrl;
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || '提交失败，请稍后重试。');
        }
    }
};

// 导出到全局
window.ClipboardApp = ClipboardApp;

// 全局提交函数（兼容旧代码）
window.submitContent = () => ClipboardApp.submitContent();
