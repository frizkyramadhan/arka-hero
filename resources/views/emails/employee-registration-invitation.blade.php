<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Employee Registration Invitation</title>
        <!--[if mso]>
        <noscript>
            <xml>
                <o:OfficeDocumentSettings>
                    <o:PixelsPerInch>96</o:PixelsPerInch>
                </o:OfficeDocumentSettings>
            </xml>
        </noscript>
        <![endif]-->
    </head>

    <body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
        <!-- Wrapper Table -->
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
            style="background-color: #f4f4f4;">
            <tr>
                <td align="center" style="padding: 20px 0;">

                    <!-- Main Container - Made wider for better Outlook compatibility -->
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="650"
                        style="max-width: 650px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">

                        <!-- Header -->
                        <tr>
                            <td align="center" style="padding: 40px 40px 30px 40px; border-bottom: 3px solid #667eea;">
                                <h1
                                    style="margin: 0; color: #667eea; font-size: 32px; font-weight: 300; font-family: Arial, sans-serif;">
                                    {{ $companyName }}</h1>
                                <p
                                    style="margin: 8px 0 0 0; color: #666666; font-size: 18px; font-family: Arial, sans-serif;">
                                    Employee Registration Invitation</p>
                            </td>
                        </tr>

                        <!-- Content -->
                        <tr>
                            <td style="padding: 40px;">
                                <h2
                                    style="margin: 0 0 20px 0; color: #333333; font-size: 24px; font-family: Arial, sans-serif;">
                                    Welcome to Our Team!</h2>

                                <p
                                    style="margin: 0 0 18px 0; color: #333333; font-size: 16px; line-height: 1.6; font-family: Arial, sans-serif;">
                                    Dear Future Team Member,</p>

                                <p
                                    style="margin: 0 0 18px 0; color: #333333; font-size: 16px; line-height: 1.6; font-family: Arial, sans-serif;">
                                    Congratulations! You have been invited to join <strong>{{ $companyName }}</strong>
                                    as our new employee. We're excited to have you on board and look forward to working
                                    with you.</p>

                                <p
                                    style="margin: 0 0 35px 0; color: #333333; font-size: 16px; line-height: 1.6; font-family: Arial, sans-serif;">
                                    To complete your employee registration, please click the button below and fill out
                                    the required information:</p>

                                <!-- CTA Button - Improved for Outlook -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                    width="100%">
                                    <tr>
                                        <td align="center" style="padding: 0 0 40px 0;">
                                            <!--[if mso]>
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $registrationUrl }}" style="height:60px;v-text-anchor:middle;width:320px;" arcsize="30%" stroke="f" fillcolor="#667eea">
                                                <w:anchorlock/>
                                                <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:18px;font-weight:bold;text-decoration:none;">Complete Your Registration</center>
                                            </v:roundrect>
                                            <![endif]-->
                                            <!--[if !mso]><!-->
                                            <a href="{{ $registrationUrl }}" target="_blank"
                                                style="display: inline-block; background-color: #667eea; color: #ffffff; text-decoration: none; padding: 18px 40px; border-radius: 30px; font-size: 18px; font-weight: bold; font-family: Arial, sans-serif; min-width: 240px; text-align: center;">Complete
                                                Your Registration</a>
                                            <!--<![endif]-->
                                        </td>
                                    </tr>
                                </table>

                                <!-- Steps Section - Improved styling with better spacing -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
                                    style="background-color: #f8f9fa; border-radius: 12px; border: 1px solid #e9ecef; margin-bottom: 30px;">
                                    <tr>
                                        <td style="padding: 30px;">
                                            <h3
                                                style="margin: 0 0 25px 0; color: #333333; font-size: 20px; font-family: Arial, sans-serif;">
                                                📋 What You'll Need to Complete:</h3>

                                            <!-- Step 1 - Fixed number centering for Outlook -->
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                                width="100%" style="margin-bottom: 25px;">
                                                <tr>
                                                    <td width="60"
                                                        style="vertical-align: top; padding-right: 20px;">
                                                        <!--[if mso]>
                                                        <v:oval xmlns:v="urn:schemas-microsoft-com:vml" style="width:36px;height:36px;" fillcolor="#667eea" stroke="f">
                                                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                                                                <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;">1</center>
                                                            </v:textbox>
                                                        </v:oval>
                                                        <![endif]-->
                                                        <!--[if !mso]><!-->
                                                        <table role="presentation" cellspacing="0" cellpadding="0"
                                                            border="0">
                                                            <tr>
                                                                <td align="center" valign="middle"
                                                                    style="background-color: #667eea; color: #ffffff; border-radius: 50%; width: 36px; height: 36px; text-align: center; vertical-align: middle; font-size: 14px; font-weight: bold; font-family: Arial, sans-serif;">
                                                                    1</td>
                                                            </tr>
                                                        </table>
                                                        <!--<![endif]-->
                                                    </td>
                                                    <td style="vertical-align: top;">
                                                        <strong
                                                            style="color: #333333; font-family: Arial, sans-serif; display: block; margin-bottom: 8px; font-size: 16px;">Personal
                                                            Information</strong>
                                                        <span
                                                            style="color: #666666; font-size: 14px; font-family: Arial, sans-serif; line-height: 1.4;">Basic
                                                            details like name, date of birth, contact information,
                                                            etc.</span>
                                                    </td>
                                                </tr>
                                            </table>

                                            <!-- Step 2 - Fixed number centering for Outlook -->
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                                width="100%" style="margin-bottom: 25px;">
                                                <tr>
                                                    <td width="60"
                                                        style="vertical-align: top; padding-right: 20px;">
                                                        <!--[if mso]>
                                                        <v:oval xmlns:v="urn:schemas-microsoft-com:vml" style="width:36px;height:36px;" fillcolor="#667eea" stroke="f">
                                                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                                                                <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;">2</center>
                                                            </v:textbox>
                                                        </v:oval>
                                                        <![endif]-->
                                                        <!--[if !mso]><!-->
                                                        <table role="presentation" cellspacing="0" cellpadding="0"
                                                            border="0">
                                                            <tr>
                                                                <td align="center" valign="middle"
                                                                    style="background-color: #667eea; color: #ffffff; border-radius: 50%; width: 36px; height: 36px; text-align: center; vertical-align: middle; font-size: 14px; font-weight: bold; font-family: Arial, sans-serif;">
                                                                    2</td>
                                                            </tr>
                                                        </table>
                                                        <!--<![endif]-->
                                                    </td>
                                                    <td style="vertical-align: top;">
                                                        <strong
                                                            style="color: #333333; font-family: Arial, sans-serif; display: block; margin-bottom: 8px; font-size: 16px;">Required
                                                            Documents</strong>
                                                        <span
                                                            style="color: #666666; font-size: 14px; font-family: Arial, sans-serif; line-height: 1.4;">Upload
                                                            KTP, CV, passport photo, and education certificates</span>
                                                    </td>
                                                </tr>
                                            </table>

                                            <!-- Step 3 - Fixed number centering for Outlook -->
                                            <table role="presentation" cellspacing="0" cellpadding="0"
                                                border="0" width="100%">
                                                <tr>
                                                    <td width="60"
                                                        style="vertical-align: top; padding-right: 20px;">
                                                        <!--[if mso]>
                                                        <v:oval xmlns:v="urn:schemas-microsoft-com:vml" style="width:36px;height:36px;" fillcolor="#667eea" stroke="f">
                                                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                                                                <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;">3</center>
                                                            </v:textbox>
                                                        </v:oval>
                                                        <![endif]-->
                                                        <!--[if !mso]><!-->
                                                        <table role="presentation" cellspacing="0" cellpadding="0"
                                                            border="0">
                                                            <tr>
                                                                <td align="center" valign="middle"
                                                                    style="background-color: #667eea; color: #ffffff; border-radius: 50%; width: 36px; height: 36px; text-align: center; vertical-align: middle; font-size: 14px; font-weight: bold; font-family: Arial, sans-serif;">
                                                                    3</td>
                                                            </tr>
                                                        </table>
                                                        <!--<![endif]-->
                                                    </td>
                                                    <td style="vertical-align: top;">
                                                        <strong
                                                            style="color: #333333; font-family: Arial, sans-serif; display: block; margin-bottom: 8px; font-size: 16px;">Review
                                                            & Submit</strong>
                                                        <span
                                                            style="color: #666666; font-size: 14px; font-family: Arial, sans-serif; line-height: 1.4;">Double-check
                                                            your information and submit for HCS review</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Security Info - Added spacing -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                    width="100%"
                                    style="background-color: #f8f9fa; border-left: 4px solid #667eea; border-radius: 8px; margin-bottom: 30px;">
                                    <tr>
                                        <td style="padding: 20px;">
                                            <h3
                                                style="margin: 0 0 12px 0; color: #667eea; font-size: 18px; font-family: Arial, sans-serif;">
                                                🔒 Secure Registration Process</h3>
                                            <p
                                                style="margin: 0; color: #666666; font-size: 15px; font-family: Arial, sans-serif; line-height: 1.5;">
                                                Your personal information is protected with enterprise-grade security.
                                                Only authorized HCS personnel can access your data.</p>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Warning Box - Added spacing -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                    width="100%"
                                    style="background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px; margin-bottom: 30px;">
                                    <tr>
                                        <td style="padding: 20px;">
                                            <p
                                                style="margin: 0; color: #856404; font-size: 15px; font-family: Arial, sans-serif; line-height: 1.5;">
                                                <strong>⏰ Important:</strong> This registration link will expire on
                                                <strong>{{ $expiresAt }}</strong>. Please complete your registration
                                                before this date.
                                            </p>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Contact Info - Added spacing -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                    width="100%" style="background-color: #e7f3ff; border-radius: 8px;">
                                    <tr>
                                        <td align="center" style="padding: 25px;">
                                            <h4
                                                style="margin: 0 0 15px 0; color: #0066cc; font-size: 18px; font-family: Arial, sans-serif;">
                                                Need Help?</h4>
                                            <p
                                                style="margin: 5px 0; color: #333333; font-size: 15px; font-family: Arial, sans-serif;">
                                                <strong>HCS Department</strong>
                                            </p>
                                            <p
                                                style="margin: 5px 0; color: #333333; font-size: 15px; font-family: Arial, sans-serif;">
                                                📧 Email: support.hrd-bpp@arka.co.id</p>
                                            <p
                                                style="margin: 5px 0; color: #333333; font-size: 15px; font-family: Arial, sans-serif;">
                                                📞 Phone: Arny (213), Nisa (153)</p>
                                            <p
                                                style="margin: 5px 0; color: #333333; font-size: 15px; font-family: Arial, sans-serif;">
                                                🕒 Office Hours: Monday - Friday, 8:00 AM - 5:00 PM</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td align="center" style="padding: 30px 40px 40px 40px; border-top: 1px solid #eeeeee;">
                                <p
                                    style="margin: 8px 0; color: #666666; font-size: 14px; font-family: Arial, sans-serif;">
                                    This email was sent to: <strong>{{ $tokenRecord->email }}</strong></p>
                                <p
                                    style="margin: 8px 0; color: #666666; font-size: 14px; font-family: Arial, sans-serif;">
                                    If you did not expect this email, please contact our HCS department immediately.</p>
                                <p
                                    style="margin: 8px 0; color: #666666; font-size: 14px; font-family: Arial, sans-serif;">
                                    &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>

</html>
