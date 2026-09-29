@php
    $data = array_merge([
        'childName' => 'John Doe',
        'parentName' => 'Jane Doe',
        'establishmentName' => 'Anytown Primary School',
        'providerName' => 'Anytown Cycling Club',
        'providerPublicEmailAddress' => 'info@anytowncyclingclub.org',
    ], $data ?? []);
@endphp

<!doctype html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>We've received {{ $data['childName'] }}'s Bikeability consent form</title>
    <!--[if mso]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
    <style>
        body,
        table,
        td,
        p,
        a {
            font-family: "Trebuchet MS", Arial, sans-serif;
        }

        table,
        td {
            border-collapse: collapse;
            mso-table-lspace: 0;
            mso-table-rspace: 0;
        }

        img {
            border: 0;
            display: block;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        @media only screen and (max-width: 620px) {
            .email-container {
                width: 100% !important;
            }

            .outer-padding {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            .content-padding {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            .email-heading {
                font-size: 29px !important;
                line-height: 35px !important;
            }

            .footer {
                padding: 14px 28px 40px !important;
            }
        }
    </style>
</head>
<body style="background-color:#fffffe;margin:0;padding:0;width:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
        We've received {{ $data['childName'] }}'s Bikeability consent form.
    </div>

    <center style="background-color:#fffffe;width:100%;">
        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td align="center">
                    <table class="email-container" role="presentation" width="600" border="0" cellspacing="0" cellpadding="0" style="margin:0 auto;width:600px;">
                        <tr>
                            <td style="font-size:0;line-height:0;">
                                <img src="{{ asset('images/emails/campaign/hero_4.jpg') }}" width="600" alt="Families enjoying cycling together" style="width:100%;max-width:600px;">
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:0;line-height:0;">
                                <img src="{{ asset('images/emails/campaign/logo_4_2.jpg') }}" width="600" alt="The Bikeability Trust" style="width:100%;max-width:600px;">
                            </td>
                        </tr>
                        <tr>
                            <td class="outer-padding" bgcolor="#ffe6c2" style="background-color:#ffe6c2;padding:22px;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="email-heading" align="center" bgcolor="#ffffff" style="background-color:#ffffff;color:#3c3c3b;font-size:36px;font-weight:700;line-height:42px;padding:34px 12px;">
                                            We've received {{ $data['childName'] }}'s Bikeability consent form!
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="outer-padding" bgcolor="#ffe6c2" style="background-color:#ffe6c2;padding:0 22px;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="content-padding" bgcolor="#ffffff" style="background-color:#ffffff;color:#3c3c3b;padding:0 32px 34px;">
                                            <p style="font-size:24px;line-height:32px;margin:0;padding:8px 0 24px;">
                                                <strong>Dear {{ $data['parentName'] }},</strong>
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                Good news! We've received your consent for {{ $data['childName'] }} to take part in a Bikeability cycle training course at {{ $data['establishmentName'] }}.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                Cycle training will be run by {{ $data['providerName'] }}, and they will be in touch with you and {{ $data['childName'] }} with the next steps.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                If you do not own your own cycle, please speak to your child's school to see if the training provider provides fleet cycles or whether they can facilitate your child borrowing another child's cycle and helmet.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                <em><strong>Please note this is an automated email and replies to this email are not monitored.</strong></em>
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0;">
                                                <em>
                                                    If you have any questions, please get in touch with
                                                    <a href="mailto:{{ $data['providerPublicEmailAddress'] }}" style="color:#ff9e1b;text-decoration:none;">{{ $data['providerPublicEmailAddress'] }}</a>.
                                                </em>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#ffe6c2" style="background-color:#ffe6c2;font-size:0;line-height:0;padding-top:22px;">
                                <img src="{{ asset('images/emails/campaign/img_13.jpg') }}" width="600" alt="Thanks for reading!" style="width:100%;max-width:600px;">
                            </td>
                        </tr>
                        <tr>
                            <td class="footer" bgcolor="#ffe6c2" style="background-color:#ffe6c2;padding:14px 50px 60px;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="color:#3c3c3b;font-size:30px;line-height:38px;padding-bottom:20px;">
                                            <strong>Follow Bikeability!</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-bottom:44px;">
                                            <table role="presentation" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <td style="font-size:0;line-height:0;">
                                                        <a href="https://x.com/BikeabilityUK" aria-label="Follow Bikeability on X" style="text-decoration:none;">
                                                            <img src="{{ asset('images/emails/campaign/ico_tw_or.jpg') }}" width="40" alt="X" style="width:40px;">
                                                        </a>
                                                    </td>
                                                    <td style="font-size:0;line-height:0;">
                                                        <a href="https://www.instagram.com/bikeabilityuk/" aria-label="Follow Bikeability on Instagram" style="text-decoration:none;">
                                                            <img src="{{ asset('images/emails/campaign/ico_in_or.jpg') }}" width="39" alt="Instagram" style="width:39px;">
                                                        </a>
                                                    </td>
                                                    <td style="font-size:0;line-height:0;">
                                                        <a href="https://www.facebook.com/BikeabilityUK/" aria-label="Follow Bikeability on Facebook" style="text-decoration:none;">
                                                            <img src="{{ asset('images/emails/campaign/ico_fb_or.jpg') }}" width="39" alt="Facebook" style="width:39px;">
                                                        </a>
                                                    </td>
                                                    <td style="font-size:0;line-height:0;">
                                                        <a href="https://www.youtube.com/c/Bikeability-Cycle-Training" aria-label="Follow Bikeability on YouTube" style="text-decoration:none;">
                                                            <img src="{{ asset('images/emails/campaign/ico_yt_or.jpg') }}" width="39" alt="YouTube" style="width:39px;">
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color:#9d917d;font-size:12px;line-height:16px;">
                                            <p style="margin:0;">
                                                <strong>Why have I received this email?</strong><br>
                                                You have received this email in relation to upcoming Bikeability cycle training.<br>
                                                You will not receive any marketing emails from us without your consent.<br>
                                                Read our
                                                <a href="https://www.bikeability.org.uk/privacy-policy/" style="color:#ff9e1b;text-decoration:none;">Privacy Policy.</a>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
