<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            direction: rtl;
            background-color: #f9f9f9;
            color: #333;
            text-align: center;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #0f898e;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
        }
        .otp-code {
            display: inline-block;
            font-size: 24px;
            background-color: #0f898e;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 20px 0;
            letter-spacing: 5px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h1>إعادة تعيين كلمة المرور</h1>
        <p>مرحباً،</p>
        <p>لقد طلبت إعادة تعيين كلمة المرور الخاصة بك. الرجاء استخدام الكود أدناه لتأكيد العملية:</p>
        <div class="otp-code">
            {{ $code }}
        </div>
        <p>إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذه الرسالة.</p>
        <p class="footer">© 2026 Avatar com. جميع الحقوق محفوظة.</p>
    </div>
</body>
</html>
