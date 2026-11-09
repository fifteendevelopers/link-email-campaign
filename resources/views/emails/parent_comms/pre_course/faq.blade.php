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
    <title>Frequently Asked Questions, answered</title>
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
        Frequently asked questions to help you get ready for {{ $data['childName'] }}'s Bikeability cycle training.
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
                                            Frequently Asked Questions, answered
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="outer-padding" bgcolor="#ffe6c2" style="background-color:#ffe6c2;padding:0 22px;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="content-padding" bgcolor="#ffffff" style="background-color:#ffffff;color:#3c3c3b;padding:8px 32px;">
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                {{ $data['childName'] }} is booked to take part in a Bikeability cycle training course at {{ $data['establishmentName'] }} - and we hope they're as excited as we are!
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0;">
                                                Here are some of our most frequently asked questions, answered, to help you get ready for {{ $data['childName'] }}'s cycle training.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#ffe6c2" align="center" style="background-color:#ffe6c2;font-size:0;line-height:0;padding-bottom:20px;">
                                <img src="{{ asset('images/emails/campaign/bikeability_clear_divider.png') }}" width="600" alt="" style="width:100%;max-width:600px;">
                            </td>
                        </tr>
                        <tr>
                            <td class="outer-padding" bgcolor="#ffe6c2" style="background-color:#ffe6c2;padding:0 22px;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="content-padding" bgcolor="#ffffff" style="background-color:#ffffff;color:#3c3c3b;padding:8px 32px;">
                                            <p style="font-size:16px;line-height:20px;margin:0 0 0;">
                                                <strong>Does my child have to bring their own cycle?</strong>
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                Yes. Please
                                                <a href="https://www.bikeability.org.uk/get-cycling/cycle-training-for-children/" style="color:#ff9e1b;text-decoration:none;"><strong>watch our 60 second guide</strong></a>
                                                on how to check it's safe and ready to ride before the course starts.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                If your child doesn't own a cycle, please speak to your child's school to see if the training provider provides fleet cycles or whether they can facilitate your child borrowing another child's cycle and helmet.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0;">
                                                <strong>What should my child wear?</strong>
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                Normal, comfortable clothes are all {{ $data['childName'] }} will need, just no baggy trousers or long coats. Don't forget to add layers in the winter and pack sunscreen in the summer.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0;">
                                                <strong>What if the weather is bad?</strong>
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                Poor weather is a part of life and so is learning to cycle in it! Bikeability cycle training takes places in all weathers, so make sure {{ $data['childName'] }} is prepared.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0;">
                                                Unless the weather is very bad, training will go ahead whatever the weather - so make sure you pack layers and waterproof clothing.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#ffe6c2" align="center" style="background-color:#ffe6c2;font-size:0;line-height:0;padding-bottom:20px;">
                                <img src="{{ asset('images/emails/campaign/bikeability_clear_divider.png') }}" width="600" alt="" style="width:100%;max-width:600px;">
                            </td>
                        </tr>
                        <tr>
                            <td class="outer-padding" bgcolor="#ffe6c2" style="background-color:#ffe6c2;padding:0 22px;">
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="content-padding" bgcolor="#ffffff" style="background-color:#ffffff;color:#3c3c3b;padding:8px 32px;">
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                <strong>Any other questions?</strong>
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                <a href="https://www.bikeability.org.uk/get-cycling/faqs/" style="color:#ff9e1b;text-decoration:none;"><strong>Visit our FAQs page</strong></a>
                                                for more questions and answers.
                                            </p>
                                            <p style="font-size:16px;line-height:20px;margin:0 0 20px;">
                                                For specific questions about Bikeability at {{ $data['establishmentName'] }} with {{ $data['providerName'] }}, please email
                                                <a href="mailto:{{ $data['providerPublicEmailAddress'] }}" style="color:#ff9e1b;text-decoration:none;">{{ $data['providerPublicEmailAddress'] }}</a>.
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
