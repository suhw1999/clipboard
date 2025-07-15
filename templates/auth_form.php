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
        margin-bottom: 20px;
        font-weight: bold;
    }
    .form-group {
        margin-bottom: 20px;
        text-align: left;
    }
    .form-label {
        color: #fff;
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }
    .form-input {
        width: 240px;
        height: 40px;
        padding: 10px;
        border: 3px solid #fff;
        border-radius: 10px;
        background: #575757;
        color: #fff;
        font-weight: bold;
        text-align: center;
        line-height: 40px;
        box-sizing: border-box;
        margin: 0 auto;
        display: block;
    }
    .form-input:focus {
        border: 3px solid #fff;
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
        margin: 15px 0;
        text-align: center;
        border: 3px solid #fff;
    }
    .back-link {
        color: #fff;
        text-decoration: none;
        margin-top: 20px;
        display: inline-block;
        font-weight: bold;
    }
    .back-link:hover {
        color: #ccc;
    }
    
    button{
        margin: 0 0px;
    }
    button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        background: #575757 !important;
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
            <div class='form-group'>
                <input type='password' 
                       id='password' 
                       name='password' 
                       class='form-input' 
                       placeholder='请输入访问密码'
                       required
                       autofocus>
            </div>
            
            <button type='submit' id='submitBtn'>
                验证
            </button>
        </form>
        
        <a href='<?php echo BASE_URL; ?>' class='back-link'>返回剪切板</a>
    </div>
    
    <script>
    document.getElementById('authForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = '验证中...';
    });
    
    // 自动聚焦到密码输入框
    document.getElementById('password').focus();
    </script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>