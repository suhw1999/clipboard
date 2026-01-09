/**
 * Clipboard 公共模块
 * 包含消息系统、AJAX封装、复制功能等
 */

// ============================================
// 消息提示系统
// ============================================
const MessageSystem = {
    container: null,
    maxMessages: 5,

    init() {
        this.container = document.getElementById('message-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'message-container';
            document.body.appendChild(this.container);
        }
    },

    show(message, type = 'success') {
        if (!this.container) this.init();

        // 限制最大消息数量
        const existingMessages = this.container.querySelectorAll('.success-message, .error-message');
        if (existingMessages.length >= this.maxMessages) {
            this.remove(existingMessages[0]);
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = type + '-message';
        messageDiv.textContent = message;
        this.container.appendChild(messageDiv);

        // 自动消失计时器
        let timeoutId = setTimeout(() => {
            this.remove(messageDiv);
        }, 3000);

        // 鼠标悬停时暂停计时器
        messageDiv.addEventListener('mouseenter', () => {
            clearTimeout(timeoutId);
        });

        messageDiv.addEventListener('mouseleave', () => {
            timeoutId = setTimeout(() => {
                this.remove(messageDiv);
            }, 1500);
        });
    },

    remove(messageElement) {
        if (!messageElement || !messageElement.parentNode) return;

        messageElement.classList.add('fade-out');

        setTimeout(() => {
            if (messageElement.parentNode) {
                messageElement.parentNode.removeChild(messageElement);
            }
        }, 300);
    },

    success(message) {
        this.show(message, 'success');
    },

    error(message) {
        this.show(message, 'error');
    }
};

// ============================================
// AJAX 请求封装
// ============================================
const API = {
    timeout: 30000, // 30秒超时

    async post(url, data, options = {}) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), options.timeout || this.timeout);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: data,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            clearTimeout(timeoutId);

            if (error.name === 'AbortError') {
                throw new Error('请求超时，请检查网络连接');
            }
            throw error;
        }
    },

    async get(url, options = {}) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), options.timeout || this.timeout);

        try {
            const response = await fetch(url, {
                method: 'GET',
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            clearTimeout(timeoutId);

            if (error.name === 'AbortError') {
                throw new Error('请求超时，请检查网络连接');
            }
            throw error;
        }
    }
};

// ============================================
// 剪贴板复制功能
// ============================================
const ClipboardUtil = {
    async copy(text) {
        // 尝试使用现代 Clipboard API
        if (navigator.clipboard && window.isSecureContext) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (err) {
                console.error('Clipboard API 失败: ', err);
                return this.fallbackCopy(text);
            }
        } else {
            // 备用方案：文本选择
            return this.fallbackCopy(text);
        }
    },

    fallbackCopy(text) {
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
            return successful;
        } catch (err) {
            console.error('备用复制方案失败: ', err);
            return false;
        } finally {
            document.body.removeChild(textArea);
        }
    }
};

// ============================================
// 工具函数
// ============================================
const Utils = {
    // 防抖函数
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // 节流函数
    throttle(func, limit) {
        let inThrottle;
        return function executedFunction(...args) {
            if (!inThrottle) {
                func(...args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};

// 导出到全局（兼容非模块环境）
window.MessageSystem = MessageSystem;
window.API = API;
window.ClipboardUtil = ClipboardUtil;
window.Utils = Utils;

// 简化的全局函数
window.showMessage = (message, type) => MessageSystem.show(message, type);
