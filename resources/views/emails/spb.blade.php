
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
   </head>
   <body style="background-color:#f9f9f9">
   
      <table style="margin: 20px auto;padding: 20px 30px" cellpadding="0" cellspacing="0" width="550" bgcolor="#ffffff">
           <tr>
              <td align="center" valign="top">
                  <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                       <td style="margin: 20px; text-align: left;" align="left">
                          <br>
                          <div style="font-size:12px;color: #333333">
                            Dear {{ $vendorPIC }}, <br>
                            {{ $vendorName }}
                            <br>
                            <br>
                            Berikut saya lampirkan Surat Pengantar Barang (SPB) <span style="font-size:13px;color: #333333;font-weight:bold;text-align:center"> {{ $companyName }}, dengan No. SPB: </span> 
                            <br>
                            <br>
                           
                            <span style="font-size:13px;color: #333333;font-weight:bold;text-align:center">{{ $no_spb }}</span>
                            <br>
                            <br>
                            Mohon agar barang dapat dikirimkan ke alamat terlampir, Harap konfirmasi pengiriman kepada pihak ekspedisi dan kami (Kontak Terlampir)
                            <br>
                            <br>

                           Note :
                           <br>
                              1. Mohon agar barang dipacking rapih,<br>
                              2. Mohon agar surat jalan di tuliskan <span style="font-size:13px;color: #333333;font-weight:bold;">NO. SPB dan NO. PO </span> nya,<br>
                              3. Mohon agar barang di tuliskan <span style="font-size:13px;color: #333333;font-weight:bold;"> PT. {{ $companyCode }}-{{ $locationName }} UNTUK NO {{ $no_spb }} </span><br>
                              4. Mohon agar <span style="font-size:13px;color: #333333;font-weight:bold;">SPB ini dilampirkan saat pengiriman dan di TTD Terima pihak kapal </span> serta scan balik ke email ini,

                            <br>
                            <br>

                            Thank & Regard’s,<br>
                            {{ $operatorName }}<br>
                            Logistic<br>
                            {{ $companyName }}
                          </div>
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