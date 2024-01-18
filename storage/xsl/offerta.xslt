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
            </style>
            <body>
                <div style="width: 630px; font-size: 10px; margin: 4px auto;">
                    <div style="text-align: center">
                        <font style="font-size: 20px;">ОФЕРТА
                            <xsl:value-of select="@num"/>
                        </font>
                        (предложение о заключении договора поставки)
                    </div>
                    <div style="text-align: left">
                        <p>
                            <xsl:choose>
                                <xsl:when test="contains(supplier, 'ПРОДАЖА ШИН')">
                                    Общество с ограниченной ответственностью «ПРОДАЖА ШИН» (ООО «ПРОДАЖА ШИН»), именуемое в
                                    дальнейшем Оферент, в лице Генерального директора Серебрякова Вадима Олеговича,
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'АвтоВояж')">
                                    Общество с ограниченной ответственностью «АвтоВояж» (ООО «АвтоВояж»), именуемое в
                                    дальнейшем Оферент, в лице Генерального директора Агаева Микаила Судеф оглы,
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'Евромотор К')">
                                    Общество с ограниченной ответственностью «Евромотор К» (ООО «Евромотор К»),
                                    именуемое в дальнейшем Оферент, в лице Генерального директора Булла Сергея
                                    Олеговича,
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'Руэда')">
                                    Общество с ограниченной ответственностью «Руэда» (ООО «Руэда»), именуемое в
                                    дальнейшем Оферент, в лице Генерального директора Сагателяна Ширака Грачиковича,
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'Гекат')">
                                    Общество с ограниченной ответственностью «Гекат» (ООО «Гекат»), именуемое в
                                    дальнейшем Оферент, в лице Генерального директора Сафиуллина Ильяса Хамзяновича,
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'Декартех')">
                                    Общество с ограниченной ответственностью «Декартех» (ООО «Декартех»), именуемое в
                                    дальнейшем Оферент, в лице Генерального директора Изюмова Евгения Олеговича,
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'МИСЕРО')">
                                    Общество с ограниченной ответственностью «МИСЕРО» (ООО «МИСЕРО»), именуемое в
                                    дальнейшем Оферент, в лице Генерального директора Михайлова Сергея Робертовича,
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'РЕНИ ЮГ')">
                                    Общество с ограниченной ответственностью «РЕНИ ЮГ» (ООО «РЕНИ ЮГ»), именуемое в
                                    дальнейшем Оферент, в лице Генерального директора Суворкова Игоря Сергеевича,
                                    <img src="{docroot}/i/of/reni_souz.jpg"/>
                                    <br/>
                                </xsl:when>
                                <xsl:otherwise>
                                    <br/>
                                </xsl:otherwise>
                            </xsl:choose>
                            действующего на основании Устава, направляет в Ваш адрес предложение о заключении договора
                            поставки (оферты) на следующих условиях:
                        </p>
                        <p>
                            1. Предмет договора: поставка автошин или (и) колесных дисков.
                        </p>
                    </div>
                    <div style="text-align: center">
                        <table style="font-size: 10px; margin: 2px auto;" width="100%">
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
                                    <strong>Итого:</strong>
                                </td>
                                <td style="text-align: right">
                                    <strong>
                                        <xsl:value-of select="total"/>
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align: right">
                                    <strong>В том числе НДС:</strong>
                                </td>
                                <td style="text-align: right">
                                    <strong>
                                        <xsl:value-of select="vat"/>
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
                    <div style="text-align: left">
                        <p>2. Поставка Товара производится в течение 5 (пяти) рабочих дней от поступления денег на счёт
                            Оферента.
                        </p>
                        <p>3. Условия поставки: Доставка транспортом Оферента по адресу в пределах МКАД (Москва), в
                            Московской области (до 10км. от МКАД) и по адресу в других регионах России (доставка
                            осуществляется до склада транспортной компании)
                            <xsl:if test="string-length(store_address)>0">, или самовывоз со склада Оферента по адресам:
                                <br/>
                                <strong>
                                    <xsl:value-of select="store_address"/>
                                </strong>
                            </xsl:if>
                            .
                        </p>
                        <p>4.
                            Услуга доставки в пределах МКАД является бесплатной. Стоимость доставки в пределах
                            Московской области рассчитывается исходя из удалённости от склада отгрузки. По территории
                            Российской Федерации доставка осуществляется до транспортной компании "ПЭК".
                            Услуги транспортной компании перевозчика оплачиваются акцептантом. Услуги транспортной
                            компании перевозчика оплачиваются оферентом при условии соответствия покупки условиям акции
                            «Бесплатная доставка по России».
                        </p>
                        <p>4.1. Исключением являются сезонные акции, и скидки на доставку товара по Московской области и
                            регионам России.
                        </p>
                        <p>5. Условия оплаты: Безналичный платёж. 100% предоплата, путём перечисления денежных средств
                            на расчётный счёт Оферента или оплата товара банковской картой.
                        </p>
                        <p>6. Подтверждением оплаты будет считаться, поступление оплаты на расчётный счёт Оферента.
                            Поступление денежных средств перечисленных Оференту с банковской карты покупателя.
                        </p>
                        <p>7. Гарантийный срок устанавливается заводом-изготовителем.</p>
                        <p>8. Переход права собственности на товар, риск случайной гибели или случайного повреждения
                            товара: с момента передачи товара транспортной компании перевозчику или акцептанту.
                        </p>
                        <p>9. Документы, предоставляемые оферентом после передачи товара: счет, товарная накладная,
                            счет-фактура.
                        </p>
                        <p>
                            <xsl:value-of select="supplier"/>
                            <br/>
                            р/с
                            <xsl:value-of select="bank_rs"/>
                            в
                            <xsl:value-of select="bank"/> БИК
                            <xsl:value-of select="bik"/> к/с
                            <xsl:value-of select="bank_ks"/>
                        </p>
                        <p>
                            Акцептант (покупатель):
                            <br/>
                            <span style="display:block; width: 100%; border-bottom: 1px solid;">
                                <xsl:value-of select="buyer_name"/>
                            </span>
                            Наименование организации / Ф.И.О. Полностью
                            <br/>
                            <br/>
                            <span style="display:block; width: 100%; border-bottom: 1px solid;">&#160;</span>
                            Адрес доставки / Адрес регистрации
                            <br/>
                        </p>
                        <p>
                            Нажатие на опцию «Отправить», означает, что Акцептант согласен со всеми положениями
                            настоящего предложения, и равносилен заключению договора поставки в соответствии с п. 2 ст.
                            432 ГК РФ.
                        </p>
                        <p>
                            <xsl:choose>
                                <xsl:when test="contains(supplier, 'ПРОДАЖА ШИН')">
                                    <img src="i/of/sst_sign_stamp.jpg"/>
                                    <br/>
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'АвтоВояж')">
                                    <img src="i/of/av_sign_stamp.jpg"/>
                                    <br/>
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'Евромотор К')">
                                    <img src="i/of/bl_sign_stamp.jpg"/>
                                    <br/>
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'Руэда')">
                                    <img src="i/of/rd_sign_stamp.jpg"/>
                                    <br/>
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'Гекат')">
                                    <img src="i/of/gk_sign_stamp.jpg"/>
                                    <br/>
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'Декартех')">
                                    <img src="i/of/dt_sign_stamp.jpg"/>
                                    <br/>
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'МИСЕРО')">
                                    <img src="i/of/mi_sign_stamp.jpg"/>
                                    <br/>
                                </xsl:when>
                                <xsl:when test="contains(supplier, 'РЕНИ ЮГ')">
                                    <img src="{docroot}/i/of/reni_souz.jpg"/>
                                    <br/>
                                </xsl:when>
                                <xsl:otherwise>
                                    <br/>
                                </xsl:otherwise>
                            </xsl:choose>
                        </p>
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

