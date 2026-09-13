<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Vérifiez votre adresse email - Lamsa</title>
    <!--[if mso]>
    <style type="text/css">
        table, td, div, p, a, h1, h2, h3 { font-family: Arial, sans-serif !important; }
    </style>
    <![endif]-->
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #F8F5F0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table {
            border-spacing: 0;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        .btn-primary:hover {
            background-color: #A84B2B !important;
        }
        @media only screen and (max-width: 600px) {
            .container-table {
                width: 100% !important;
                padding: 16px !important;
            }
            .content-cell {
                padding: 24px 20px !important;
            }
            .btn-table {
                width: 100% !important;
            }
            .btn-primary {
                display: block !important;
                padding: 14px 20px !important;
                font-size: 15px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #F8F5F0; color: #2D3748;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="background-color: #F8F5F0; min-height: 100vh; padding: 40px 0;">
        <tr>
            <td align="center" style="padding: 0 16px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="container-table" style="max-width: 580px; margin: 0 auto;">
                    
                    <!-- BRAND HEADER -->
                    <tr>
                        <td align="center" style="padding-bottom: 24px;">
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td align="center">
                                        <div style="font-size: 28px; font-weight: 800; letter-spacing: 3px; color: #C25E38; text-transform: uppercase;">
                                            ✦ LAMSA ✦
                                        </div>
                                        <div style="font-size: 13px; font-weight: 500; letter-spacing: 1.5px; color: #8C7B6B; text-transform: uppercase; margin-top: 4px;">
                                            L'Artisanat Marocain d'Excellence
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- MAIN CARD -->
                    <tr>
                        <td style="background-color: #FFFFFF; border-radius: 16px; border: 1px solid #EAE3D9; box-shadow: 0 4px 20px rgba(194, 94, 56, 0.06); overflow: hidden;">
                            
                            <!-- DECORATIVE TOP STRIP -->
                            <div style="height: 5px; background: linear-gradient(90deg, #C25E38 0%, #D4A373 50%, #C25E38 100%);"></div>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                                <tr>
                                    <td class="content-cell" style="padding: 40px 36px;">
                                        
                                        <!-- BADGE -->
                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 20px;">
                                            <tr>
                                                <td style="background-color: #FDF3E7; border: 1px solid #F6D6B8; border-radius: 20px; padding: 4px 14px;">
                                                    <span style="color: #C25E38; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                                        Vérification de sécurité
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- GREETING -->
                                        <h1 style="margin: 0 0 16px 0; font-size: 22px; font-weight: 700; color: #1E293B; line-height: 1.3;">
                                            Bienvenue sur Lamsa, {{ $user->name }} !
                                        </h1>

                                        <!-- MESSAGE -->
                                        <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 1.6; color: #475569;">
                                            Merci d'avoir rejoint notre communauté dédiée au savoir-faire et à la richesse de l'artisanat marocain. 
                                            Pour activer votre compte en toute sécurité, veuillez confirmer votre adresse email en cliquant sur le bouton ci-dessous.
                                        </p>

                                        <!-- CTA BUTTON -->
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin: 30px 0;">
                                            <tr>
                                                <td align="center">
                                                    <table border="0" cellpadding="0" cellspacing="0" class="btn-table" role="presentation">
                                                        <tr>
                                                            <td align="center" style="border-radius: 10px; background-color: #C25E38;">
                                                                <a href="{{ $url }}" class="btn-primary" target="_blank" style="display: inline-block; padding: 15px 36px; font-size: 16px; font-weight: 700; color: #FFFFFF; text-decoration: none; border-radius: 10px; letter-spacing: 0.3px; background-color: #C25E38; border: 1px solid #C25E38;">
                                                                    Confirmer mon adresse email →
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- EXPIRATION NOTICE BOX -->
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="background-color: #FBF9F5; border-radius: 10px; border-left: 4px solid #C25E38; margin-bottom: 28px;">
                                            <tr>
                                                <td style="padding: 14px 16px;">
                                                    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #64748B;">
                                                        <strong style="color: #1E293B;">⏱️ Validité du lien :</strong> Ce lien de confirmation est sécurisé et expire dans <strong style="color: #C25E38;">60 minutes</strong>.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- FALLBACK URL BOX -->
                                        <div style="border-top: 1px solid #F1ECE4; padding-top: 20px; margin-top: 10px;">
                                            <p style="margin: 0 0 8px 0; font-size: 12px; color: #94A3B8; line-height: 1.4;">
                                                Si le bouton ne fonctionne pas, copiez et collez ce lien sécurisé dans votre navigateur :
                                            </p>
                                            <div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 10px 12px; word-break: break-all;">
                                                <a href="{{ $url }}" style="color: #C25E38; font-size: 12px; text-decoration: underline; line-height: 1.4;">
                                                    {{ $url }}
                                                </a>
                                            </div>
                                        </div>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td align="center" style="padding: 30px 20px 0 20px;">
                            <p style="margin: 0 0 10px 0; font-size: 12px; color: #8C7B6B; line-height: 1.5;">
                                Si vous n'êtes pas à l'origine de cette inscription, vous pouvez ignorer cet email en toute sécurité.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #A09080;">
                                &copy; {{ date('Y') }} <strong>Lamsa Inc.</strong> — Tous droits réservés.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
