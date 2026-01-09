<?php
$page_title = '身份验证';
require_once dirname(__DIR__) . '/includes/header.php';
?>
    <div class='auth-container'>
        <h1 class='auth-title'>身份验证</h1>
        
        <?php if (isset($errorMessage)): ?>
            <div class='error-message'><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <form class='auth-form' method='POST' id='authForm'>
            <input type='password'
                   id='password'
                   name='password'
                   class='form-input'
                   placeholder='请输入访问密码'
                   required
                   autofocus>
            <nav>
                <ul>
                    <li id='submitBtn' onclick='document.getElementById("authForm").submit()'>
                        验证
                        <span></span><span></span><span></span><span></span>
                    </li>
                </ul>
            </nav>
        </form>
        
        <nav>
            <ul>
                <a href='<?php echo BASE_URL; ?>'><li>
                    返回剪切板
                    <span></span><span></span><span></span><span></span>
                </li></a>
            </ul>
        </nav>
    </div>
    
    <script>
    document.getElementById('authForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.style.pointerEvents = 'none';
        submitBtn.style.opacity = '0.6';
        const spans = submitBtn.querySelectorAll('span');
        submitBtn.textContent = '验证中...';
        spans.forEach(span => submitBtn.appendChild(span));
    });
    </script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>