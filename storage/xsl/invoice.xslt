<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <!-- XML output mode -->
    <xsl:output method="html" standalone="yes" indent="no" encoding="utf-8"/>

    <!-- we do not need spaces in output file -->
    <xsl:strip-space elements="*"/>

    <xsl:template match="/invoice">
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <title>Счет</title>
            </head>
            <style>
                body {
                    font-family: "DejaVu Sans";
                    font-size:  10pt;
                    line-height:  1.1;
                }
                td {
                    border: 1px solid;
                    padding: 4px;
                }
                p { background-image: url({docroot}/php.gif); background-position: top left; background-repeat: repeat-x; }
            </style>
            <body>
                <div style="width: 630px; font-size: 11px; margin: 4px auto;">
                    <div style="text-align: center; margin-top: 8px; margin-bottom:8px;">
                        Внимание! Оплата данного счета означает согласие с условиями поставки товара.
                        <br/>
                        Товар отпускается по факту прихода денег на прихода денег на р/с Поставщика, самовывозом или
                        доставкой
                        при наличии доверенности и паспорта.
                    </div>
                    <div style="text-align: center">
                        <strong>Образец заполнения платежного поручения</strong>
                        <table style="font-size: 11px; margin: 0px auto;" width="100%">
                            <tr>
                                <td rowspan="2">Получатель
                                    <br/>
                                    <xsl:value-of select="supplier_company"/>
                                </td>
                                <td>&#160;</td>
                                <td>&#160;</td>
                            </tr>
                            <tr>
                                <td>Р/С №</td>
                                <td>
                                    <xsl:value-of select="bank_rs"/>
                                </td>
                            </tr>
                            <tr>
                                <td rowspan="2">Банк получателя
                                    <br/>
                                    <xsl:value-of select="bank"/>
                                </td>
                                <td>БИК</td>
                                <td>
                                    <xsl:value-of select="bik"/>
                                </td>
                            </tr>
                            <tr>
                                <td>К/С №</td>
                                <td>
                                    <xsl:value-of select="bank_ks"/>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <br/>
                    <div>
                        <h1 style="text-align: left; font-style: normal;">Счет №
                            <xsl:choose>
                                <xsl:when test="string-length(invoice_number) !=  0">
                                    <xsl:value-of select="invoice_number"/>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="@num"/>
                                </xsl:otherwise>
                            </xsl:choose>
                            от
                            <xsl:value-of select="@from"/>
                        </h1>
                        <hr size="1"/>
                    </div>
                    <div style="text-align: center">
                        <table style="font-size: 11px; margin: 0px auto;" width="100%">
                            <tr>
                                <td>Поставщик:</td>
                                <td>
                                    <strong>
                                        <xsl:value-of select="supplier"/>
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td>Покупатель:</td>
                                <td>
                                    <strong>
                                        <xsl:value-of select="buyer"/>
                                    </strong>
                                </td>
                            </tr>
                        </table>
                        <br/>
                        <table style="font-size: 11px; margin: 2px auto;" width="100%">
                            <tr>
                                <td style="text-align: center">
                                    <strong>№</strong>
                                </td>
                                <td style="text-align: center">
                                    <strong>Товар</strong>
                                </td>
                                <td style="text-align: center">
                                    <strong>Кол-во</strong>
                                </td>
                                <td style="text-align: center">
                                    <strong>Ед.</strong>
                                </td>
                                <td style="text-align: center">
                                    <strong>Цена</strong>
                                </td>
                                <td style="text-align: center">
                                    <strong>Сумма</strong>
                                </td>
                            </tr>
                            <xsl:apply-templates select="items/item"/>
                            <tr>
                                <td colspan="2" rowspan="2">Резерв действителен 5 банковских дней</td>
                                <td colspan="3" style="text-align: right">
                                    <strong>В том числе НДС:</strong>
                                </td>
                                <td style="text-align: right">
                                    <strong>
                                        <xsl:value-of select="vat"/>
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align: right">
                                    <strong>Итого:</strong>
                                </td>
                                <td style="text-align: right">
                                    <strong>
                                        <xsl:value-of select="total"/>
                                    </strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <br/>
                    <div>
                        Всего наименований <xsl:value-of select="count(items/item)"/>, на сумму
                        <xsl:value-of select="total"/>
                        <br/>
                        <strong>
                            <xsl:value-of select="total_text"/>
                        </strong>
                    </div>
                    <hr size="1"/>
                    <div>
                        Объем заказанного товара составляет примерно:
                        <xsl:value-of select="volume"/>
                        <br/>
                    </div>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <p>
                        <xsl:choose>
                            <xsl:when test="contains(supplier, 'ПРОДАЖА ШИН')">
                                <img src="{docroot}/sst_sign_stamp.jpg"/>
                                <br/>
                            </xsl:when>
                            <xsl:when test="contains(supplier, 'АвтоВояж')">
                                <img src="{docroot}/av_sign_stamp.jpg"/>
                                <br/>
                            </xsl:when>
                            <xsl:when test="contains(supplier, 'Евромотор К')">
                                <img src="{docroot}/bl_sign_stamp.jpg"/>
                                <br/>
                            </xsl:when>
                            <xsl:when test="contains(supplier, 'Руэда')">
                                <img src="{docroot}/rd_sign_stamp.jpg"/>
                                <br/>
                            </xsl:when>
                            <xsl:when test="contains(supplier, 'Гекат')">
                                <img src="{docroot}/gk_sign_stamp.jpg"/>
                                <br/>
                            </xsl:when>
                            <xsl:when test="contains(supplier, 'Декартех')">
                                <img src="{docroot}/dt_sign_stamp.jpg"/>
                                <br/>
                            </xsl:when>
                            <xsl:when test="contains(supplier, 'МИСЕРО')">
                                <img src="{docroot}/mi_sign_stamp.jpg"/>
                                <br/>
                            </xsl:when>
                            <xsl:when test="contains(supplier, 'РЕНИ ЮГ')">
                                <img src="{docroot}/reni_souz.jpg"/>
                                <br/>
                            </xsl:when>
                            <xsl:otherwise>
                                <br/>
                            </xsl:otherwise>
                        </xsl:choose>
                    </p>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <div style="width: 622px; text-align: center; margin: 4px auto; border: 1px solid;">
                        <strong>
                            При оформлении платежного документа обязательно указывать
                            <br/>
                            1. Наименование плательщика (Ф.И.О.) платежа;
                            <br/>
                            2. В графе "Назначение платежа" указать:
                            <br/>
                            Оплата по счету №
                            <xsl:choose>
                                <xsl:when test="string-length(invoice_number) !=  0">
                                    <xsl:value-of select="invoice_number"/>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="@num"/>
                                </xsl:otherwise>
                            </xsl:choose>
                            от <xsl:value-of select="@from"/>, в том числе НДС <xsl:value-of select="vat"/>.
                            <br/>
                            3. При оплате третьими лицами в графе "Назначение платежа" указать:
                            <br/>
                            Оплата по счету №
                            <xsl:choose>
                                <xsl:when test="string-length(invoice_number) !=  0">
                                    <xsl:value-of select="invoice_number"/>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="@num"/>
                                </xsl:otherwise>
                            </xsl:choose>
                            от <xsl:value-of select="@from"/>, в том числе НДС <xsl:value-of select="vat"/>.
                            <br/>
                            за
                            <xsl:value-of select="buyer"/>
                        </strong>
                    </div>
                </div>
                <div style="margin:15px 15px 15px 15px;">
                    <div class="section vertical">
                        <xsl:apply-templates select="help-topics"/>
                        <div class="box visible">
                            <xsl:value-of select="help-text" disable-output-escaping="yes"/>
                        </div>
                    </div>
                </div>
            </body>
        </html>

    </xsl:template>

    <xsl:template match="items/item">
        <tr>
            <td style="text-align: right">
                <xsl:value-of select="@num"/>
            </td>
            <td>
                <xsl:value-of select="name"/>
            </td>
            <td style="text-align: right">
                <xsl:value-of select="amount"/>
            </td>
            <td>
                <xsl:value-of select="measure"/>
            </td>
            <td style="text-align: right">
                <xsl:value-of select="price"/>
            </td>
            <td style="text-align: right">
                <xsl:value-of select="total"/>
            </td>
        </tr>
    </xsl:template>

</xsl:stylesheet>

