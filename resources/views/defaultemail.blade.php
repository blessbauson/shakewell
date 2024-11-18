<!DOCTYPE html>
<html lang="en">
    <head>
        <title></title>
    </head>
    <body>
        <table>
            <tbody>
                <tr>
                    <td>
                        <div style="text-align: center"><h1>Shakewell</h1></div>
                    </td>
                </tr>
                <tr><td></td></tr>
                <tr>
                    <td><p>Hello {{$params['name']}},</p></td>
                </tr>
                <tr><td></td></tr>
                <tr>
                    <td><p>{!! html_entity_decode($params['body']) !!}</p></td>
                </tr>
                <tr><td></td></tr>
                <tr>
                    <td><span>Best regards,</span></td>
                </tr>
                <tr>
                    <td><span>Shakewell Team</span></td>
                </tr>
                <tr><td></td></tr>
                <tr>
                    <td><p>This is a system-generated message. Please do not reply.</p></td>
                </tr>
                <tr><td></td></tr>
                <tr>
                    <td><p>Need help? Contact  our support team at support@shakewell.com.</p></td>
                </tr>
                <tr><td></td></tr>
                <tr><td></td></tr>
                <tr>
                    <td><p style="font-size:8px; font-style: italic;">This email contains confidential information for the safe use of the intended recipient/s. If you are not  the intended  recipient, please contact the sender, delete this email, and maintain  the confidentiality of what you may have read.</p></td>
                </tr>
                <tr><td></td></tr>
            
            </tbody>
        </table>

    </body>
</html> 