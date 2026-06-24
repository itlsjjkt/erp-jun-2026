
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
   </head>
   <body style="background-color:#f9f9f9">
      <table style="margin: 30px auto 0;" cellpadding="0" cellspacing="0" width="550">
         <tr>
            <td valign="top" style="padding:20px 0px">
              <img src="http://cmos.citamineral.com/images/cmos.png">
            </td>
         </tr>
      </table>
      <table style="margin: 10px auto;padding: 20px 30px" cellpadding="0" cellspacing="0" width="550" bgcolor="#ffffff">
           <tr>
              <td align="center" valign="top">
                  <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                       <td>
                          <center>
                          <br>
                          <h3>{{ $title }}</h3>
                          </center>
                       </td>
                    </tr>
                  </table>
                  <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                       <td style="margin: 20px; text-align: left;" align="left">
                          <br>
                          <div style="font-size:12px;color: #333333">
                            Berikut Daftar Produk Urgent Stock setelah dilakukan TTB dengan No: {{ $no_ttb }} <br><br>
                           
                            <table width="100%" border="1" style="border-collapse:collapse;color:#1f2240;">
                              <tr>
                                 <th> No</th>
                                 <th> Kode </th>
                                 <th> Produk </th>
                                 <th> Stok</th>
                                 <th> Min</th>
                                 <th> Max</th>
                              <tr>
                              @php 
                                 $no = 1;
                              @endphp
                              @foreach ($product as $item)
                                 <tr>
                                    <td style="text-align:center"> {{ $no }}</td>
                                    <td> {{ $item['productCode'] }}</td>
                                    <td> {{ $item['productName'] }}</td>
                                    <td style="text-align:center"> {{ $item['stock'] }}</td>
                                    <td style="text-align:center"> {{ $item['min'] }}</td>
                                    <td style="text-align:center"> {{ $item['max'] }}</td>
                                 </tr>
                                 @php 
                                    $no++;
                                 @endphp
                              @endforeach
                           </table>
                            <br>
                          </div>
                          <br>
                       </td>
                    </tr>
                  </table>
                 <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                       <td>
                          <span style="font-size:12px;color: #333333">
                           Terima-Kasih. <br>
                          </span>
                          <br>
                         <br>
                       </td>
                    </tr>
                 </table>
              </td>
           </tr>
      </table>

       <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="550" >
           <tr>
              <td valign="top" >
                <center>
                     <br>
                     <span style="font-size:12px;color: #757575">
                       Copyright @ 2019 - <a href="http://citamineral.com"  style="font-size:12px;color: #757575">Cita Mineral Investindo</a>. All rights reserved.
                       <br>
                        Panin Bank Building Lantai 2 Jakarta Selatan<br>
                        Jl. Jend Sudirman – Senayan, Jakarta Pusat 10270 <br>
                        Telp.: +62 21 7251344  | Email: corsec@citamineral.com
                       <br>
                       <br>
                     </span>
                </center>
              </td>
          </tr>
       </table>
   </body>
</html>