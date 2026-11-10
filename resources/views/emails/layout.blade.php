<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sahabat Sekolah' }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f5f8fc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e3a58; -webkit-font-smoothing: antialiased; }
        table { border-collapse: collapse; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f5f8fc; padding: 30px 0; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 12px; border: 1px solid #e1eaf2; overflow: hidden; box-shadow: 0 4px 16px rgba(18, 56, 106, 0.05); }
        .header { background: linear-gradient(135deg, #11366a 0%, #092b57 100%); padding: 26px 32px; color: #ffffff; text-align: left; }
        .header-title { font-size: 22px; font-weight: 800; margin: 0; color: #ffffff; letter-spacing: -0.02em; }
        .header-sub { font-size: 13px; color: #a4c2e6; margin-top: 4px; margin-bottom: 0; }
        .content { padding: 32px; }
        .footer { padding: 22px 32px; background-color: #f8fafc; border-top: 1px solid #edf2f7; text-align: center; font-size: 13px; color: #8295a8; line-height: 1.6; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-danger { background-color: #ffe5e6; color: #c93b44; }
        .badge-warning { background-color: #fff0d9; color: #b87514; }
        .badge-success { background-color: #e3f8ef; color: #1c7c59; }
        .badge-info { background-color: #e5f2ff; color: #1a6fc4; }
        .info-card { background-color: #f8fafc; border: 1px solid #e9edf2; border-radius: 8px; padding: 18px 20px; margin: 20px 0; }
        .info-row td { padding: 7px 0; font-size: 15px; vertical-align: top; }
        .info-label { color: #6b7f94; font-weight: 500; width: 38%; }
        .info-value { color: #1e3a58; font-weight: 600; }
        .btn-primary { display: inline-block; background-color: #1878e3; color: #ffffff !important; font-size: 15px; font-weight: 700; text-decoration: none; padding: 12px 26px; border-radius: 8px; text-align: center; }
        .alert-box { border-radius: 8px; padding: 14px 18px; margin: 18px 0; font-size: 14px; line-height: 1.5; }
        .alert-warning { background-color: #fff9eb; border: 1px solid #fae6b8; color: #8f600e; }
        .alert-info { background-color: #edf6ff; border: 1px solid #cfe5fc; color: #185a9c; }
        .quote-box { background-color: #fbfcfe; border-left: 3px solid #1878e3; padding: 12px 16px; margin: 14px 0; font-size: 15px; line-height: 1.6; color: #2d4c6b; font-style: italic; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main" width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="header">
                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                        <tr>
                            <td>
                                <div style="display: inline-block; vertical-align: middle; margin-right: 10px;">
                                    <div style="width: 28px; height: 28px; border-radius: 7px; background: #ffffff; color: #1878e3; font-weight: 900; font-size: 19px; line-height: 28px; text-align: center;">S</div>
                                </div>
                                <div style="display: inline-block; vertical-align: middle;">
                                    <h1 class="header-title">SAHABAT SEKOLAH</h1>
                                    <p class="header-sub">Platform Penanganan Perundungan Kota Bukittinggi</p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="content">
                    @yield('content')
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p style="margin: 0 0 6px;">Email ini dikirimkan secara otomatis oleh Sistem <strong>Sahabat Sekolah</strong>.<br>Mohon tidak membalas email ini secara langsung.</p>
                    <p style="margin: 0; color: #9aa9b8; font-size: 12px;">Data laporan dilindungi oleh kebijakan privasi & kerahasiaan identitas siswa Kota Bukittinggi.</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
