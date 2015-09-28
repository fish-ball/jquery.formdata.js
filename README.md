jquery.formdata.js
==================

Overview
--------

This is a jQuery plugin which supports an advanced ajax call method, which has
the same interface of the standard `$.ajax` method one. 

The `$.ajaxFormData` method introduced in this plugin extends the field type of
data object, which was passed as a ajax option.

In the standard `$.ajax` method, the data field only supports standard value of
type string/number/boolean or so, but this `$.ajaxFormData` also supports field
of type File/Blob/Array/Base64Url, and upload the data stream field as a upload
field, which can be read as a normal file uploaded in the backend.

This plugin do not use the FormData HTML5 object, neither the Blob constructor,
but fully constructs the post payload manually, so it works well with a number
of browsers which may not support these features. But, it requires the 
FileReader and Uint8Array Interface, browsers which do not support the two 
method may not use it well.

Usage
-----

### Download the plugin

You can install the code via npm or bower:

```
npm install jquery-formdata
```

Or:

```
bower install jquery-formdata
```

After the library was downloaded, you got the `jquery.formdata.js` script file,
that's all.

Of course, you can directly download the file from the git repository.

### Include the code

You can also include the script file directly with the html markup, or use AMD
module to introduce the code:

#### 1. Directly with `<script>` tag:

```html
<!DOCTYPE html>
<html>
<head>
    <script type="text/javascript" src="jquery.min.js"></script>
    <script type="text/javascript" src="jquery.formdata.js"></script>
</head>
<body>
    <input id="upload" type="file" name="gallery[]" multiple />
    <script>
    $(function() {
        $('#upload').change(function() {
            $.ajaxFormData('/api/', {
                method: 'post',
                data: {
                    'username': 'fish-ball',
                    'active_users[]': [1, 2, 4],
                    'gallery[]': this.files
                }
            });
        });
    });
    </script>
</body>
</html>
```

In this way, you must first introduce `jquery`, then `jquery.formdata.js`. The
code after that can use the `$.ajaxFormData` method.

#### 2. With AMD system such as `require.js`:

```javascript
require.config({
    paths: {
        'jquery': 'jquery/jquery.min.js',
        'jquery.formdata': 'jquery/jquery.formdata.js'
    },
    shim: {
        'jquery': { exports: 'jQuery' },
        'jquery.formdata': { deps: ['jquery'] }
    }
});

require(['jquery', 'jquery.formdata'], function($) {
    $.ajaxFormData({
        url: '/api/',
        method: 'post',
        data: {
            // ...
        }
    });
});
```

### The `$.ajaxFormData` method

The `$.ajaxFormData` takes url (optional) and options with the same interface
with the standard `$.ajax` method, but it accepts only for unsafe HTTP methods.

That means, if using `method: 'get'`, the `$.ajaxForm` may bypass the ajax
request to a standard `$.ajax` method, and do NOT deal with the advance fields.

More, in any case of `$.ajaxFormData` expected, it only accepts data in the 
form of standard javascript object, do not accept raw string, array buffer or
`FormData` objects.

And the request header `Content-Type` is always set as `multipart/form-data`,
so the `contentType` option and `processData` option is omitted.

All in all, you only need to remember, use a 'POST/PUT/PATCH/DELETE' method,
always pass data as an object, other detail is no need to care about.

More, the `$.ajaxFormData` also returns a jQuery Promise object, which can
register `.done/.fail/.success` or other deferred usage. And the callback args
is just the same as the `$.ajax` ones.

### Examples

#### 1. Single file field

```html
<!DOCTYPE html>
<html>
<head>
    <title>test01.html</title>
    <script type="text/javascript" src="jquery.min.js"></script>
    <script type="text/javascript" src="../jquery.formdata.js"></script>
</head>
<body>
    <input id="upload" type="file" name="avatar" />
    <a id="submit" href="javascript:;">Submit</a>
    <script>
    $(function() {
        $('#submit').click(function() {
            $.ajaxFormData({
                url: 'test01.api.php',
                method: 'post',
                data: {
                    name: 'test01',
                    avatar: document.getElementById('upload').files[0]
                },
                success: function(data, textStatus, jqXHR) {
                    alert(data);
                }, 
                error: function(jqXHR, textStatus, errorThrown) {
                    alert(jqXHR.responseText);
                }
            });
        });
    });
    </script>
</body>
</html>
```

And then, select some file, click the submit link, then the backend receives
the request:

```php
<?php
// test01.api.php
assert($_POST['name'] == 'test01');
var_dump($_FILES);
```

Then the frontend alerts the result:

```
array(1) {
  ["avatar"]=>
  array(5) {
    ["name"]=>
    string(15) "art-study.local"
    ["type"]=>
    string(24) "application/octet-stream"
    ["tmp_name"]=>
    string(14) "/tmp/phpHCFD4M"
    ["error"]=>
    int(0)
    ["size"]=>
    int(1906)
  }
}
```

#### 2. Base64 image url field

If a field is a string matches a base64 image url pattern, it will be deal
as a file.

The below example draws a picture on a canvas, then posts the base64 string
from `canvas.toDataUrl` to the backend.

```html
<!DOCTYPE html>
<html>
<head>
    <title>test02.html</title>
    <script type="text/javascript" src="jquery.min.js"></script>
    <script type="text/javascript" src="../jquery.formdata.js"></script>
</head>
<body>
    <canvas id="canvas" width="600" height="480"></canvas>
    <a id="submit" href="javascript:;">Submit</a>
    <script>
    $(function() {
        // example from http://www.atopon.org/mandel/
        var canvas = document.getElementById('canvas');
        var context = canvas.getContext('2d');
        var width = canvas.width ,height = canvas.height;
        var maxIterations = 100;
        var minRe = -2.0;
        var maxRe = 1.0;
        var minIm = -1;
        var maxIm = minIm+(maxRe-minRe)*height/width;

        context.fillRect(0, 0, width, height);
        var imgd = context.getImageData(0, 0, width, height)
        var pix = imgd.data;

        var drawPixel = function (x, y, itr) {
            var i = (y * width + x) * 4;
            pix[i] = pix[i + 1] = pix[i + 2] = Math.round(itr * 255 / maxIterations);
        };

        mandelbrot(width, height, drawPixel);
        context.putImageData(imgd, 0, 0);

        function mandelbrot(imageWidth, imageHeight, drawPixel) {
            var re_factor = (maxRe-minRe)/(imageWidth-1);
            var im_factor = (maxIm-minIm)/(imageHeight-1);
            for(var y=0; y<imageHeight; ++y) {
                var c_im = maxIm - y*im_factor;
                for(var x=0; x<imageWidth; ++x) {
                    var c_re = minRe + x*re_factor;
                    var z_re = c_re, z_im = c_im;
                    var isInside = true;
                    var n = 0;
                    for(; n<maxIterations; ++n) {
                        var z_re2 = z_re*z_re, z_im2 = z_im*z_im;
                        if(z_re2 + z_im2 > 4) {
                            isInside = false;
                            break;
                        }
                        z_im = 2*z_re*z_im + c_im;
                        z_re = z_re2 - z_im2 + c_re;
                    }
                    if (!isInside) { drawPixel(x, y, n); }
                }
            }
        }

        $('#submit').click(function() {
            $.ajaxFormData({
                url: 'test02.api.php',
                method: 'post',
                data: {
                    name: 'test02',
                    picture: canvas.toDataURL()
                },
                success: function(data, textStatus, jqXHR) {
                    alert(data);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert(jqXHR.responseText);
                }
            });
        });
    });
    </script>
</body>
</html>
```

The backend receives the field as a file, and move it to the current folder.

After the action, you can found the saved image file: `picture.png`.

```
<?php
// test02.api.php
assert($_POST['name'] == 'test02');
var_dump($_FILES);

rename($_FILES['picture']['tmp_name'], 'picture.png');
```
