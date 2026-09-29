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
    <title>Check your cycle is ready for Bikeability</title>
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

        a {
            color: #ff9e1b;
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

            .mobile-column {
                display: block !important;
                width: 100% !important;
            }

            .mobile-image {
                height: auto !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            .card-content {
                padding: 24px 20px !important;
            }

            .email-heading {
                font-size: 29px !important;
                line-height: 35px !important;
            }

            .section-heading {
                font-size: 22px !important;
                line-height: 29px !important;
            }

            .card-spacing {
                padding-bottom: 20px !important;
            }

            .footer {
                padding: 14px 28px 40px !important;
            }
        }
    </style>
</head>
<body style="background-color:#fffffe;margin:0;padding:0;width:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
        Three quick checks to help {{ $data['childName'] }} get ready for Bikeability.
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
                                            Check your cycle is ready for Bikeability
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
                                                Good news! {{ $data['childName'] }} is booked to take part in a Bikeability cycle training course at {{ $data['establishmentName'] }}.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0;">
                                                If you have been asked to provide your own cycle, follow our top tips to ensure your child is ready to ride.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#ffe6c2" style="background-color:#ffe6c2;padding:20px 22px 0;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="section-heading" bgcolor="#ffffff" style="background-color:#ffffff;color:#3c3c3b;font-size:24px;font-weight:700;line-height:32px;padding:16px 32px;">
                                            Getting ready for Bikeability is easy as 1, 2, 3...
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="outer-padding" bgcolor="#ffe6c2" style="background-color:#ffe6c2;padding:0 22px;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="card-spacing" style="padding-bottom:40px;">
                                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <th class="mobile-column" width="274" valign="top" style="font-weight:normal;padding:0;text-align:left;width:274px;">
                                                        <a href="https://www.bikeability.org.uk/get-cycling/cycle-training-for-children/" style="text-decoration:none;">
                                                            <img class="mobile-image" src="{{ asset('images/emails/campaign/CycleCheck.165723.png') }}" width="274" height="274" alt="A child checking that their cycle is ready to ride" style="height:274px;width:274px;">
                                                        </a>
                                                    </th>
                                                    <th class="mobile-column" width="274" valign="middle" bgcolor="#ffffff" style="background-color:#ffffff;font-weight:normal;padding:0;width:274px;">
                                                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                            <tr>
                                                                <td class="card-content" align="center" style="color:#3c3c3b;padding:22px;">
                                                                    <p style="font-size:24px;line-height:28px;margin:0 0 24px;">
                                                                        <strong>1. <a href="https://www.bikeability.org.uk/get-cycling/cycle-training-for-children/" style="color:#3c3c3b;text-decoration:none;">Watch our cycle check video</a></strong>
                                                                    </p>
                                                                    <p style="font-size:16px;line-height:20px;margin:0 0 24px;">
                                                                        Watch our 60 second video to make sure your child's cycle is ready to ride.
                                                                    </p>
                                                                    <table role="presentation" border="0" cellspacing="0" cellpadding="0" align="center">
                                                                        <tr>
                                                                            <td bgcolor="#ff9e1b" style="background-color:#ff9e1b;border-radius:7px;text-align:center;">
                                                                                <a href="https://www.bikeability.org.uk/get-cycling/cycle-training-for-children/" style="color:#3c3c3b;display:inline-block;font-size:16px;font-weight:700;line-height:20px;padding:13px 24px;text-decoration:none;">Watch here</a>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </th>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="card-spacing" style="padding-bottom:40px;">
                                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" dir="rtl">
                                                <tr>
                                                    <th class="mobile-column" width="274" valign="top" style="font-weight:normal;padding:0;text-align:left;width:274px;">
                                                        <a href="https://www.bikeability.org.uk/get-cycling/cycle-training-for-children/" style="text-decoration:none;">
                                                            <img class="mobile-image" src="{{ asset('images/emails/campaign/HelmetCheck.165815.png') }}" width="274" height="274" alt="A child checking the fit of their cycle helmet" style="height:274px;width:274px;">
                                                        </a>
                                                    </th>
                                                    <th class="mobile-column" width="274" valign="middle" bgcolor="#ffffff" dir="ltr" style="background-color:#ffffff;font-weight:normal;padding:0;width:274px;">
                                                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                            <tr>
                                                                <td class="card-content" align="center" style="color:#3c3c3b;padding:22px;">
                                                                    <p style="font-size:24px;line-height:28px;margin:0 0 24px;">
                                                                        <strong>2. <a href="https://www.bikeability.org.uk/get-cycling/cycle-training-for-children/" style="color:#3c3c3b;text-decoration:none;">Fit your helmet properly</a></strong>
                                                                    </p>
                                                                    <p style="font-size:16px;line-height:20px;margin:0 0 24px;">
                                                                        Find out how to fit your child's helmet properly in less than a minute.
                                                                    </p>
                                                                    <table role="presentation" border="0" cellspacing="0" cellpadding="0" align="center">
                                                                        <tr>
                                                                            <td bgcolor="#ff9e1b" style="background-color:#ff9e1b;border-radius:7px;text-align:center;">
                                                                                <a href="https://www.bikeability.org.uk/get-cycling/cycle-training-for-children/" style="color:#3c3c3b;display:inline-block;font-size:16px;font-weight:700;line-height:20px;padding:13px 24px;text-decoration:none;">Watch here</a>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </th>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="card-spacing" style="padding-bottom:40px;">
                                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <th class="mobile-column" width="274" valign="top" style="font-weight:normal;padding:0;text-align:left;width:274px;">
                                                        <a href="https://www.bikeability.org.uk/get-cycling/faqs/" style="text-decoration:none;">
                                                            <img class="mobile-image" src="{{ asset('images/emails/campaign/ReadyToRideCheck.170009.png') }}" width="274" height="274" alt="A helmeted child ready to ride their cycle" style="height:274px;width:274px;">
                                                        </a>
                                                    </th>
                                                    <th class="mobile-column" width="274" valign="middle" bgcolor="#ffffff" style="background-color:#ffffff;font-weight:normal;padding:0;width:274px;">
                                                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                            <tr>
                                                                <td class="card-content" align="center" style="color:#3c3c3b;padding:22px;">
                                                                    <p style="font-size:24px;line-height:28px;margin:0 0 24px;">
                                                                        <strong>3. <a href="https://www.bikeability.org.uk/get-cycling/faqs/" style="color:#3c3c3b;text-decoration:none;">Your child is ready to ride!</a></strong>
                                                                    </p>
                                                                    <p style="font-size:16px;line-height:20px;margin:0 0 24px;">
                                                                        But if you have any other questions, take a look at our FAQs.
                                                                    </p>
                                                                    <table role="presentation" border="0" cellspacing="0" cellpadding="0" align="center">
                                                                        <tr>
                                                                            <td bgcolor="#ff9e1b" style="background-color:#ff9e1b;border-radius:7px;text-align:center;">
                                                                                <a href="https://www.bikeability.org.uk/get-cycling/faqs/" style="color:#3c3c3b;display:inline-block;font-size:16px;font-weight:700;line-height:20px;padding:13px 24px;text-decoration:none;">FAQs</a>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </th>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="outer-padding" bgcolor="#ffe6c2" style="background-color:#ffe6c2;padding:0 22px;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="content-padding" bgcolor="#ffffff" style="background-color:#ffffff;color:#3c3c3b;padding:16px 32px;">
                                            <p style="font-size:16px;line-height:20px;margin:0 0 12px;">
                                                Please note this is an automated email and replies to this email are not monitored.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0;">
                                                If you have any questions, please get in touch with
                                                <a href="mailto:{{ $data['providerPublicEmailAddress'] }}" style="color:#ff9e1b;text-decoration:underline;">{{ $data['providerPublicEmailAddress'] }}</a>.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
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
                                                <a href="https://www.bikeability.org.uk/privacy-policy/" style="color:#ff9e1b;text-decoration:underline;">Privacy Policy.</a>
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
