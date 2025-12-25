<?php
$page_title = '身份验证';
$additional_head_content = '
    <style type="text/css">
    .auth-container {
        background: #575757;
        border: 3px solid #fff;
        border-radius: 10px;
        padding: 30px;
        max-width: 400px;
        width: 300px;
        text-align: center;
        margin: 30px;
    }
    .auth-title {
        color: #fff;
        font-size: 24px;
        margin-bottom: 15px;
        font-weight: bold;
    }
    .form-input {
        width: 240px;
        height: 60px;
        border: 3px solid #fff;
        border-radius: 10px;
        background: #575757;
        color: #fff;
        font-weight: bold;
        text-align: center;
        line-height: 60px;
        margin: 0;
    }
    .form-input:focus {
        outline: none;
    }
    .form-input::placeholder {
        color: #ccc;
    }
    .error-message {
        background: #f44336;
        color: #fff;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 20px;
        border: 3px solid #fff;
    }
    .auth-container nav ul {
        display: flex;
        justify-content: center;
    }
    .auth-container nav ul li {
        margin: 20px 0 0 0;
    }
    @media (max-width: 480px) {
        .auth-container {
            width: 280px;
            margin: 20px;
            padding: 20px;
        }
        .form-input {
            width: 200px;
        }
        .auth-title {
            font-size: 20px;
        }
    }
    </style>';
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