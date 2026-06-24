<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Name</th>
            <th>NIK</th>
            <th>Keterangan</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Selesai</th>
        </tr>
    </thead>
    <tbody>
    @php $no = 1 @endphp
    @foreach($employee_status as $val)
        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ $val->name }}</td>
            <td>{{ $val->nik }}</td>
            <td>{{ $val->keterangan }}</td>
            <td>{{ $val->mulai }}</td>
            <td>{{ $val->selesai }}</td>
            
        </tr>
    @endforeach
    </tbody>
</table>