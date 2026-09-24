<table class="table">
    <thead class="thead-light">
      <tr>
        <th scope="col">#</th>
        <th scope="col">No Mahasiswa</th>
        <th scope="col">Nama</th>
        <th scope="col">Angkatan</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($mahasiswa as $item)
            <tr>
                <th scope="row">{{$loop->iteration}}</th>
                <td>{{$item->no_mhs}}</td>
                <td>{{$item->nama_mahasiswa}}</td>
                <td>{{$item->angkatan}}</td>
            </tr>
        @endforeach
      
    </tbody>
  </table>