<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>{{ config('app.name') }} - Access Denied</title>
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="font-family:'Poppins',sans-serif; background:#f4f6fb; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0;">
  <div style="text-align:center; padding:2rem;">
    <img src="{{ asset('images/waytrackclosed.png') }}" alt="{{ config('app.name') }}" style="height:64px; width:auto; margin-bottom:1.5rem;">
    <h1 style="font-size:5rem; font-weight:700; color:#07326A; margin:0; line-height:1;">403</h1>
    <h2 style="font-size:1.25rem; font-weight:600; color:#333; margin:0.5rem 0 1rem;">Access Denied</h2>
    <p style="color:#6c757d; margin-bottom:2rem;">You do not have permission to view this page.</p>
    <a href="{{ url('/') }}" class="btn-action" style="text-decoration:none; font-weight:400; text-transform:none; color:#ffffff;">Back to Home</a>
  </div>
</body>
</html>
