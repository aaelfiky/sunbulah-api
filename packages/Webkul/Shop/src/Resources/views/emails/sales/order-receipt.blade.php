<!DOCTYPE html>
<html lang="en" dir="rtl">
<div id="email" style="width:800px;margin: auto;background:white;">
    <table role="presentation" border="0" align="right" cellspacing="0">
        <tr>
            <td>

            </td>
        </tr>
    </table>

    <!-- Header -->
    <table role="presentation" border="0" width="100%" cellspacing="20">
        <tr>
            <td>
                <img alt="Flower" src="{{asset('images/barcode.gif')}}" width="100px">
            </td>
            <td width="800" align="right">
                <img alt="Flower" src="{{asset('images/sunbulah.png')}}" width="150px">
                <h4>شركة صناعات الأغذية والعجائن الفاخرة (السنبلة)</h4>
            </td>

            <td style="color: white;">
                <img alt="Flower" src="{{asset('images/qr-code.png')}}" width="180px">
            </td>
        </tr>
    </table>


    <table role="presentation" border="0" align="right" width="100%" cellspacing="0">
        <tr>
            <td align="right" style="padding: 30px 30px 30px 60px;">
                <span><b>رقم الفاتورة:</b> #{{$order->id}}</span><br>
                <span>تاريخ الطلب: {{$order->created_at}}</span><br>
                <span>حالة الطلب: {{$order->status_label}}</span><br>
            </td>
            <td width="90"></td>
            <td width="90"></td>
            <td align="right" style="padding: 30px 30px 30px 60px;">
                <span><b>طريقة الشحن : Sunbulah Delivery</b></span><br>
                <span>عنوان الشحن: السعودية</span><br>
            </td>

        </tr>

        <tr>
            <td align="right" style="padding: 0 30px;">
                <span>اسم العميل: {{$order->customer_full_name}}</span><br>
                <span>رقم الجوال: {{$order->customer?->phone ?? "N/A"}}</span><br>
            </td>
            <td width="90"></td>
            <td width="90"></td>
            <td align="right" style="padding: 0 30px">
                <span><b>طريقة الدفع:</b> الدفع عند الاستلام</span><br>
            </td>
        </tr>
    </table>

    <br />
    <br />
    <br />
    <table role="presentation" border="0" margin="20" align="right" width="100%" cellspacing="0">
        <tr height="50">
            <td></td>
        </tr>
    </table>


    <table role="presentation" border="0" align="right" width="100%" cellspacing="0">
        <thead align="right" height="40" style="padding: 10px; margin-bottom: 20px" bgcolor="#F0F0F0">
            <tr style="text-align: right; margin:20px; color: #B59410;">
                <th>#</th>
                <th>اسم المنتج / رمز المنتج</th>
                <th>الوزن</th>
                <th>(قبل الضريبة) السعر</th>
                <th>الضريبة</th>
                <th>(بعد الضريبة) السعر</th>
                <th>الكمية</th>
                <th>الإجمالى</th>
            </tr>
        </thead>
        <tbody align="right" margin="20" style="text-align: right; margin:20px">
            <tr height="10"></tr>
            @foreach($order->all_items as $item)
            <tr>
                <td>{{$item->id}}</td>
                <td>{{$item->name}}</td>
                <td>{{$item->weight}}</td>
                <td>{{$item->base_price}}</td>
                <td>{{$item->base_tax_amount}}</td>
                <td>{{$item->total}}</td>
                <td>{{$item->qty_ordered}}</td>
                <td>{{$item->total}}</td>
            </tr>
            @endforeach
            <tr height="10">
                <td></td>
            </tr>
        </tbody>
        <tfoot height="30" style="padding: 20px;">
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="background:#F0F0F0; color: #B59410;"> <b>الإجمالى</b></td>
                <td style="background:#F0F0F0;">SAR {{$order->sub_total}}</td>
            </tr>
        </tfoot>
    </table>

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

</html>