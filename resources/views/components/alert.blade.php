@if (Session::has('error'))
    <script>
        alertify.set('notifier', 'position', 'top-center');
        alertify.error("{{ session('error') }}").delay(10);
    </script>
@elseif(Session::has('success'))
    <script>
        alertify.set('notifier', 'position', 'top-right');
        alertify.success("{{ session('success') }}");
    </script>
@endif

@if ($errors->any())
    <script>
        alertify.set('notifier', 'position', 'top-right');
        var errorMessage = "";
        @foreach ($errors->all() as $error)
            errorMessage += "{{ $error }}, \n";
        @endforeach
        alertify.error(errorMessage);
    </script>
@endif
