<!DOCTYPE html>
<html lang="en" dir="rtl">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
<style>
@font-face {
    font-family: "Amiri";
    /* src: url("/fonts/Amiri-Regular.ttf") format('truetype'); */
    font-weight: normal;
    font-style: normal;
}
@font-face {
    font-family: "Amiri";
    /* src: url("/fonts/Amiri-Bold.ttf") format('truetype'); */
    font-weight: bold;
    font-style: normal;
}
body {
    font-family: "Amiri";
}
h4 {
    margin: 0;
}
.w-full {
    width: 100%;
}
.w-half {
    width: 50%;
}
.w-third {
    width: 33%;
}
.margin-top {
    margin-top: 1.25rem;
}
.footer {
    font-size: 0.875rem;
    padding: 1rem;
    background-color: rgb(241 245 249);
}
table {
    width: 100%;
    border-spacing: 0;
}
table.products {
    font-size: 0.875rem;
}
table.products tr {
    background-color: rgb(96 165 250);
}
table.products th {
    color: #ffffff;
    padding: 0.5rem;
}
table tr.items {
    background-color: rgb(241 245 249);
}
table tr.items td {
    padding: 0.5rem;
}
.total {
    text-align: right;
    margin-top: 1rem;
    font-size: 0.875rem;
}

#summary {
    padding-top: 180px;
}
</style>
<body>
    <table class="w-full">
        <tr>
            <td class="w-half">
                <img src="./images/qr-code.png" width="180px">
            </td>
            <td class="w-half">
                <img style="padding:20px" width="180px"/>
            </td>
            <td align="right" class="w-half">
                <img src="./images/sunbulah.jpg" alt="laravel daily" width="200" />
            </td>
            <td class="w-half">
                <img src="./images/barcode.gif" width="100px">
            </td>
        </tr>
        <tr>
            <td class="w-full"></td>
            <td class="w-half"></td>
            <!-- <td align="right" class="w-third">
                شركة صناعات الأغذية والعجائن الفاخرة (السنبلة)
            </td> -->
        </tr>
    </table>
 
    <div class="margin-top">
        <table class="w-full">
            <tr class="w-half">
                <td align="right">
                    <span><b>رقم الفاتورة:</b> #{{$order->id}}</span><br>
                    <span>تاريخ الطلب: {{$order->created_at}}</span><br>
                    <span>حالة الطلب: {{$order->status_label}}</span><br>
                </td>
                <td width="90"></td>
                <td width="90"></td>
                <td width="90"></td>
                <td width="90"></td>
                <td align="right">
                    <span><b>طريقة الشحن : Sunbulah Delivery</b></span><br>
                    <span>عنوان الشحن: السعودية</span><br>
                </td>
            </tr>

            <tr class="w-half">
                <td align="right">
                    <span>اسم العميل: {{$order->customer_full_name}}</span><br>
                    <span>رقم الجوال: {{$order->customer?->phone ?? "N/A"}}</span><br>
                </td>
                <td width="90"></td>
                <td width="90"></td>
                <td width="90"></td>
                <td width="90"></td>
                <td align="right">
                    <span><b>طريقة الدفع:</b> الدفع عند الاستلام</span><br>
                </td>
            </tr>
        </table>
        <!-- <table class="w-full">
            <tr>
                <td class="w-half">
                    <div><h4>To:</h4></div>
                    <div>John Doe</div>
                    <div>123 Acme Str.</div>
                </td>
                <td class="w-half">
                    <div><h4>From:</h4></div>
                    <div>Laravel Daily</div>
                    <div>London</div>
                </td>
            </tr>
        </table> -->
       
    </div>

    

    
 
    <div class="margin-top">
        <table class="products">
            <tr style="background-color:#F0F0F0; text-align: right; margin:20px; color: #B59410;">
                <th style="color: #B59410;">الإجمالى</th>
                <th style="color: #B59410;">الكمية</th>
                <th style="color: #B59410;">(بعد الضريبة) السعر</th>
                <th style="color: #B59410;">الضريبة</th>
                <th style="color: #B59410;">(قبل الضريبة) السعر</th>
                <th style="color: #B59410;">الوزن</th>
                <th style="color: #B59410;">اسم المنتج / رمز المنتج</th>
                <th style="color: #B59410;">#</th>
            </tr>
            @foreach($order->all_items as $item)
            <tr class="items">
                <td>{{$item->total}}</td>
                <td>{{$item->qty_ordered}}</td>
                <td>{{$item->total}}</td>
                <td>{{$item->base_tax_amount}}</td>
                <td>{{$item->base_price}}</td>
                <td>{{$item->weight}}</td>
                <td>{{$item->name}}</td>
                <td>{{$item->id}}</td>
            </tr>
            @endforeach

            <tr>
                <td style="background:#F0F0F0;">SAR {{$order->sub_total}}</td>
                <td style="background:#F0F0F0; color: #B59410;"> <b>الإجمالى</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        
        </table>
    </div>
 
    <div class="total">
        <h5>
            تفاصيل الأسعار
        </h5>
        <table align="right" cellspacing="0">
            <tr>
                <td width="400">المجموع غير شامل الضريبة</td>
                <td align="left" width="90"> SAR {{$order->sub_total}} </td>
            </tr>
            <tr height="2" width="20">
                <td width="50" style="background:#F0F0F0;"></td>
                <td width="50" style="background:#F0F0F0;"></td>
            </tr>
            <tr height="8">
                <td></td>
            </tr>
            <tr>
                <td width="400"> التوصيل</td>
                <td align="left" width="90"> SAR {{$order->shipping_amount}} </td>
            </tr>
            <tr height="2" width="20">
                <td width="50" style="background:#F0F0F0;"></td>
                <td width="50" style="background:#F0F0F0;"></td>
            </tr>
            <tr height="8">
                <td></td>
            </tr>

            <tr>
                <td width="400"> ضريبة القيمة المضافة</td>
                <td align="left" width="90"> SAR {{$order->tax_amount}} </td>
            </tr>


            <tr height="8">
                <td></td>
            </tr>
            <tr height="8">
                <td></td>
            </tr>

            <tr height="2" width="20">
                <td width="50" style="background:#B59410;"></td>
                <td width="50" style="background:#B59410;"></td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr height="8">
                <td></td>
            </tr>
            <tr height="8">
                <td></td>
            </tr>

            <tr>
                <td style="color: #B59410;" width="400"><b>المجموع الكلى</b></td>
                <td style="color: #B59410;" align="left" width="90"> SAR {{$order->base_grand_total}} </td>
            </tr>
        </table>
    </div>
 
    <!-- <div class="footer margin-top">
        <div>Thank you</div>
        <div>&copy; Laravel Daily</div>
    </div> -->
</body>
</html>