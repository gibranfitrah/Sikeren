<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
        <title>Laravel 8: Dynamic Dependent Dropdown</title>
    </head>
    <body>
        <div class="container my-5">
            <h1 class="fs-5 fw-bold my-4 text-center">How to Create Dependent Dropdown in Laravel</h1>
            <div class="row">
                <form action="">
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" name="" id="category">
                            <option hidden>Choose Category</option>
                            @foreach ($category as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="course" class="form-label">Course</label>
                        <select class="form-control" name="course" id="course"></select>
                    </div>
                </form>
            </div>
        </div>
        <script src="assets/vendor/jquery/jquery.min.js"></script>
        <script>
            $(document).ready(function() {
            $('#category').on('change', function() {
               var categoryID = $(this).val();
               if(categoryID) {
                   $.ajax({
                       url: 'getCourse/'+categoryID,
                       type: "GET",
                       data : {"_token":"{{ csrf_token() }}"},
                       dataType: "json",
                       success:function(data)
                       {
                         if(data){
                            $('#course').empty();
                            $('#course').append('<option hidden>Choose Course</option>'); 
                            $.each(data, function(key, course){
                                $('select[name="course"]').append('<option value="'+ key +'">' + course.name+ '</option>');
                            });
                        }else{
                            $('#course').empty();
                        }
                     }
                   });
               }else{
                 $('#course').empty();
               }
            });
            });
        </script>
    </body>
</html>
