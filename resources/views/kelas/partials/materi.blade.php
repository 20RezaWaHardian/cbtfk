<table class="table">
    <thead class="thead-light">
      <tr>
        <th scope="col">#</th>
        <th scope="col">Materi</th>
        <th scope="col">Dosen Pengampu</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $item)
            <tr>
                <th scope="row">{{$item->nomor_urut}}</th>
                <td>{{$item->materi_perkuliahan}}</td>
                <td>
                  @foreach ($item->rincian as $dt)
                    <ul>
                      <ol>{{$dt->gelar_depan}}{{$dt->nama}}{{$dt->gelar_belakang}}</ol>
                    </ul>
                  @endforeach
                  
                </td>
            </tr>
        @endforeach
      
    </tbody>
  </table>