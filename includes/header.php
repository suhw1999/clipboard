<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <title><?php echo isset($page_title) ? $page_title : 'Clipboard'; ?></title>
    <link rel="stylesheet" href="<?php echo isset($css_path) ? $css_path : ''; ?>css.css">
    <link rel="shortcut icon" type="image/x-icon" href="https://suhw1999.cn/favicon.ico">
    <?php if (isset($additional_head_content)) echo $additional_head_content; ?>
</head>
<body<?php echo isset($body_class) ? ' class="' . htmlspecialchars($body_class) . '"' : ''; ?>>