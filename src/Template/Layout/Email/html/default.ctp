<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    </head>
    <body>
        <table cellspacing="0" cellpadding="0" border="0" width="100%" style="font-family: Verdana, Helvetica, Arial;">
            <tr>
                <td bgcolor="#FFFFFF" align="center">
                    <table width="650px" cellspacing="0" cellpadding="3">
                        <tr>
                            <td>
                                <?= $this->fetch('content') ?>

                                <p>
                                    <br />
                                    <strong>
                                        <?= $this->Html->image('ici-email-logo.jpg', [
                                            'alt' => 'Ball State Indiana Communities Institute',
                                            'fullBase' => true
                                        ]) ?>
                                    </strong>
                                    <br />
                                    <a href="http://www.bsu.edu/ici">
                                        http://www.bsu.edu/ici
                                    </a>
                                    <br />
                                    <a href="mailto:cri@bsu.edu">
                                        cri@bsu.edu
                                    </a>
                                    &#149; 765-285-2773
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>