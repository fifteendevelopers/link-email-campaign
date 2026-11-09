@php
    $data = array_merge([
        'establishmentName' => 'Anytown Primary School',
        'providerName' => 'Anytown Cycling Club',
        'providerContactEmail' => 'info@anytowncyclingclub.org',
        'bookingLink' => url('/'),
    ], $data ?? []);
@endphp

<!doctype html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Complete Consent Checks &amp; Finalise Numbers</title>
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
            color: #f9423a;
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

            .footer {
                padding: 14px 28px 40px !important;
            }
        }
    </style>
</head>
<body style="background-color:#fffffe;margin:0;padding:0;width:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
        Upcoming Bikeability - action required! Complete consent checks and finalise numbers for {{ $data['establishmentName'] }}.
    </div>

    <center style="background-color:#fffffe;width:100%;">
        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td align="center">
                    <table class="email-container" role="presentation" width="600" border="0" cellspacing="0" cellpadding="0" style="margin:0 auto;width:600px;">
                        <tr>
                            <td style="font-size:0;line-height:0;">
                                <img src="{{ asset('images/emails/campaign/hero_3.jpg') }}" width="600" alt="Children cycling with Bikeability instructors" style="width:100%;max-width:600px;">
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:0;line-height:0;">
                                <a href="https://www.bikeability.org.uk/" style="text-decoration:none;">
                                    <img src="{{ asset('images/emails/campaign/logo_4_2.jpg') }}" width="600" alt="The Bikeability Trust" style="width:100%;max-width:600px;">
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="outer-padding" bgcolor="#ffcecd" style="background-color:#ffcecd;padding:22px;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="email-heading" align="center" bgcolor="#ffffff" style="background-color:#ffffff;color:#3c3c3b;font-size:36px;font-weight:700;line-height:42px;padding:34px 12px;">
                                            Complete Consent Checks &amp; Finalise Numbers
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="outer-padding" bgcolor="#ffcecd" style="background-color:#ffcecd;padding:0 22px;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="content-padding" bgcolor="#ffffff" style="background-color:#ffffff;color:#3c3c3b;padding:8px 32px;">
                                            <p style="font-size:20px;line-height:26px;margin:0 0 20px;">
                                                <strong>Upcoming Bikeability - action required!</strong>
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                1. It is essential that you check for outstanding consent forms and chase parents who are yet to complete the required information. Children won't be able to take part without a completed form.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                2. Confirm your final booked numbers in advance of your forthcoming Bikeability sessions.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                Your final booked number is the number that you anticipate will be attending the course. This helps your training provider plan for the right number of instructors and ensures the correct grant funding can be claimed.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0;">
                                                <a href="{{ $data['bookingLink'] }}" style="color:#f9423a;text-decoration:none;"><strong>Complete Consent Checks &amp; Finalise Numbers</strong></a>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="outer-padding" bgcolor="#ffcecd" style="background-color:#ffcecd;padding:0 22px;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="content-padding" bgcolor="#ffffff" style="background-color:#ffffff;color:#3c3c3b;padding:8px 32px;">
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                If the link does not work, or if you require any further information, please contact {{ $data['providerName'] }} by emailing
                                                <a href="mailto:{{ $data['providerContactEmail'] }}" style="color:#f9423a;text-decoration:none;">{{ $data['providerContactEmail'] }}</a>.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0;">
                                                <em>Please note this is an automated email and replies to this email are not monitored.</em>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#ffcecd" align="center" style="background-color:#ffcecd;font-size:0;line-height:0;padding-bottom:20px;">
                                <img src="{{ asset('images/emails/campaign/bikeability_clear_divider.png') }}" width="600" alt="" style="width:100%;max-width:600px;">
                            </td>
                        </tr>
                        <tr>
                            <td class="outer-padding" bgcolor="#ffcecd" style="background-color:#ffcecd;padding:0 22px;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="content-padding" bgcolor="#ffffff" style="background-color:#ffffff;color:#3c3c3b;padding:8px 32px;">
                                            <p style="font-size:16px;line-height:20px;margin:0 0 8px;">
                                                <strong>Top tip!</strong>
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0;">
                                                If you are providing cycles for your pupils, it's important to check that they are fit and safe to ride before Bikeability cycle training begins.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="outer-padding" bgcolor="#ffcecd" style="background-color:#ffcecd;padding:0 22px 40px;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <th class="mobile-column" width="274" valign="top" style="font-weight:normal;padding:0;text-align:left;width:274px;">
                                            <a href="https://www.bikeability.org.uk/get-cycling/cycle-training-for-children/" style="text-decoration:none;">
                                                <img class="mobile-image" src="{{ asset('images/emails/campaign/CycleCheck.132428.png') }}" width="274" height="274" alt="A child checking that their cycle is ready to ride" style="height:274px;width:274px;">
                                            </a>
                                        </th>
                                        <th class="mobile-column" width="274" valign="middle" bgcolor="#ffffff" style="background-color:#ffffff;font-weight:normal;padding:0;width:274px;">
                                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <td class="card-content" align="center" style="color:#3c3c3b;padding:22px;">
                                                        <p style="font-size:24px;line-height:28px;margin:0 0 24px;">
                                                            <a href="https://www.bikeability.org.uk/get-cycling/cycle-training-for-children/" style="color:#f9423a;text-decoration:none;"><strong>Watch our cycle check video</strong></a>
                                                        </p>
                                                        <p style="font-size:16px;line-height:20px;margin:0 0 24px;">
                                                            Watch our 60 second video to make sure your school's cycles are ready to ride.
                                                        </p>
                                                        <table role="presentation" border="0" cellspacing="0" cellpadding="0" align="center">
                                                            <tr>
                                                                <td bgcolor="#f9423a" style="background-color:#f9423a;border-radius:4px;text-align:center;">
                                                                    <a href="https://www.bikeability.org.uk/get-cycling/cycle-training-for-children/" style="color:#3c3c3b;display:inline-block;font-size:20px;font-weight:700;line-height:26px;padding:15px;text-decoration:none;">Watch here</a>
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
                            <td class="footer" bgcolor="#ffcecd" style="background-color:#ffcecd;padding:14px 50px 60px;">
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
                                                            <img src="{{ asset('images/emails/campaign/ico_tw_rd.jpg') }}" width="40" alt="X" style="width:40px;">
                                                        </a>
                                                    </td>
                                                    <td style="font-size:0;line-height:0;">
                                                        <a href="https://www.instagram.com/bikeabilityuk/" aria-label="Follow Bikeability on Instagram" style="text-decoration:none;">
                                                            <img src="{{ asset('images/emails/campaign/ico_in_rd.jpg') }}" width="39" alt="Instagram" style="width:39px;">
                                                        </a>
                                                    </td>
                                                    <td style="font-size:0;line-height:0;">
                                                        <a href="https://www.facebook.com/BikeabilityUK/" aria-label="Follow Bikeability on Facebook" style="text-decoration:none;">
                                                            <img src="{{ asset('images/emails/campaign/ico_fb_rd.jpg') }}" width="39" alt="Facebook" style="width:39px;">
                                                        </a>
                                                    </td>
                                                    <td style="font-size:0;line-height:0;">
                                                        <a href="https://www.youtube.com/c/Bikeability-Cycle-Training" aria-label="Follow Bikeability on YouTube" style="text-decoration:none;">
                                                            <img src="{{ asset('images/emails/campaign/ico_yt_rd.jpg') }}" width="39" alt="YouTube" style="width:39px;">
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
                                                <a href="https://www.bikeability.org.uk/privacy-policy/" style="color:#f9423a;text-decoration:none;">Privacy Policy.</a>
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
