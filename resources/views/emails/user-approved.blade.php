<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
    .container { max-width: 600px; margin: 0 auto; padding: 30px 20px; }
    .header { background: #07326A; color: #fff; padding: 20px 24px; border-radius: 4px 4px 0 0; }
    .header h1 { margin: 0; font-size: 20px; }
    .body { background: #f9f9f9; padding: 24px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 4px 4px; }
    .btn { display: inline-block; background: #07326A; color: #fff !important; padding: 10px 24px;
           text-decoration: none; border-radius: 4px; margin-top: 16px; }
    .footer { margin-top: 20px; font-size: 12px; color: #888; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>{{ config('app.name') }}</h1>
    </div>
    <div class="body">
      <p>Dear {{ $user->name }},</p>
      <p>
        Your account registration has been <strong>approved</strong>.
        You can now log in and start using the system.
      </p>
      <a href="{{ route('login') }}" class="btn">Log In Now</a>
      <p class="footer">
        If you did not create an account, please ignore this email.<br>
        &copy; {{ date('Y') }} {{ config('app.name') }}
      </p>
    </div>
  </div>
</body>
</html>
