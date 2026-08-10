<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Super Admin - Acceso</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: 'Segoe UI', Arial, sans-serif; background: #0f172a; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0;">

    <div style="background: #1e293b; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 8px 30px rgba(0,0,0,0.5);">

        <div style="text-align: center; margin-bottom: 24px;">
            <div style="width: 56px; height: 56px; background: #2563eb; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M12 2 2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/>
                    <path d="M2 12l10 5 10-5"/>
                </svg>
            </div>
            <h2 style="color: #f1f5f9; margin: 0; font-size: 22px;">Panel Super Admin</h2>
            <p style="color: #94a3b8; margin: 6px 0 0; font-size: 13px;">Gestión de tenants y configuración global</p>
        </div>

        @if ($errors->any())
            <div style="background: #7f1d1d; color: #fecaca; padding: 12px; border-radius: 6px; margin-bottom: 16px; font-size: 14px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.login.store') }}">
            @csrf

            <div style="margin-bottom: 16px;">
                <label style="color: #cbd5e1; display: block; margin-bottom: 6px; font-size: 13px;">Correo</label>
                <div style="display: flex; align-items: center; background: #0f172a; border: 1px solid #334155; border-radius: 6px; padding: 0 12px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" style="flex-shrink:0;">
                        <path d="M4 4h16v16H4z" stroke="none"/>
                        <path d="M22 6l-10 7L2 6"/>
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                    </svg>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        style="width: 100%; padding: 10px 8px; border: none; background: transparent; color: #f1f5f9; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="color: #cbd5e1; display: block; margin-bottom: 6px; font-size: 13px;">Contraseña</label>
                <div style="display: flex; align-items: center; background: #0f172a; border: 1px solid #334155; border-radius: 6px; padding: 0 12px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" style="flex-shrink:0;">
                        <rect x="3" y="11" width="18" height="10" rx="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <input type="password" name="password" required
                        style="width: 100%; padding: 10px 8px; border: none; background: transparent; color: #f1f5f9; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <label style="display: flex; align-items: center; gap: 8px; color: #cbd5e1; font-size: 13px; margin-bottom: 24px; cursor: pointer;">
                <input type="checkbox" name="remember" value="1">
                Mantener sesión
            </label>

            <button type="submit"
                style="width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer;">
                Iniciar Sesión
            </button>
        </form>

    </div>

</body>
</html>