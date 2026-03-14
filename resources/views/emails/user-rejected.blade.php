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
        Thank you for registering with {{ config('app.name') }}.
      </p>
      <p>
        After review, we are unable to approve your account at this time.
        If you believe this is an error, please contact the administrator directly.
      </p>
      <p class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }}
      </p>
    </div>
  </div>
</body>
</html>
