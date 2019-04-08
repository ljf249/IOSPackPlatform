<html lang="zh-CN"><head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IOS App打包专用</title>
    <link href="style/css/bootstrap.min.css" rel="stylesheet">
    <link href="style/css/bootstrap-fileinput.css" rel="stylesheet">
</head>
<body style="">
<div class="container">
    <div class="page-header">
        <h3>IOS App打包</h3>
        <form id="uploadForm" enctype="multipart/form-data">

            <div class="form-group">
                <div class="h4">IPA</div>
                <div class="fileinput fileinput-new" >

                    <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"></div>
                    <div>
                        <span class="btn btn-primary btn-file">
                        <span class="fileinput-new">选择IPA文件</span>
                        <span class="fileinput-exists">换一个</span>
                        <input type="file" name="ipa" id="ipaID"  >
                        </span>
                        <a href="javascript:;" class="btn btn-warning fileinput-exists" data-dismiss="fileinput">移除</a>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="h4">图标预览(image)</div>
                <div class="fileinput fileinput-new" data-provides="fileinput" id="exampleInputUpload">
                    <div class="fileinput-new thumbnail" style="width: 200px;height: auto;max-height:150px;display: none">
                        <img id="picImg" style="width: 100%;height: auto;max-height: 140px;" src="images/noimage.png" alt="">
                    </div>
                    <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"></div>
                    <div>
                        <span class="btn btn-primary btn-file">
                        <span class="fileinput-new">选择文件</span>
                        <span class="fileinput-exists">换一张</span>
                        <input type="file" name="icon" id="iconID" accept="image/gif,image/jpeg,image/x-png">
                        </span>
                        <a href="javascript:;" class="btn btn-warning fileinput-exists" data-dismiss="fileinput">移除</a>
                    </div>
                </div>
            </div>




            <div class="form-group">
                <div class="h4">App包名(bundle-identifier)</div>
                <div class="fileinput fileinput-new" >
                    <input type="text" name="AppPackageName">
                </div>
            </div>

            <div class="form-group">
                <div class="h4">版本号(bundle-version)</div>
                <div class="fileinput fileinput-new" >
                    <input type="text" name="AppVersion">
                </div>
            </div>

            <div class="form-group">
                <div class="h4">应用名称(App Subtitle)</div>
                <div class="fileinput fileinput-new" >
                    <input type="text" name="AppName">
                </div>
            </div>




            <button type="button" id="uploadSubmit" class="btn btn-info">提交</button>
            <br><br>
            <span id="msgShow" style="color: red"></span>
            <br><br>
            <div id="anyOperation" hidden>
                <a  title="点击查看链接" target="_blank" id="plistLink" >plist</a>

                <br>

                <button title="点击复制链接"  id="copyLink" type="button"  class="btn btn-info">复制</button>
                <div id="c" hidden>请先上传文件</div>
            </div>

        </form>
    </div>
</div>
<script src="https://libs.baidu.com/jquery/1.10.2/jquery.min.js"></script>
<script src="style/js/bootstrap-fileinput.js"></script>
<script src="style/js/clipboard.min.js"></script>
<script type="text/javascript">
    $(function () {







        //比较简洁，细节可自行完善
        $('#uploadSubmit').click(function () {
            $("#msgShow").html('上传中...');
            var data = new FormData($('#uploadForm')[0]);
            $.ajax({
                url: 'upload_plist.php',
                type: 'POST',
                data: data,
                dataType: 'JSON',
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (json) {
                    console.log(json);
                    if(json.code == 0){
                        alert(json.data.msg);
                        $("#msgShow").html(json.data.msg);
                    }else if(json.code == 1){
                        $("#anyOperation").show();
                        $("#msgShow").html("完成");
                        $("#plistLink").attr("href",json.data.plist);
                        $("#c").html(json.data.prefix+json.data.plist);


                        var c = document.getElementById("c");
                        var s=c.innerHTML;
                        var clipboard = new Clipboard('#copyLink', {
                            text: function() {
                                alert(s)
                                return s;
                            }
                        });

                        clipboard.on('success', function(e) {
                            alert("复制成功");
                            console.log(e)
                        });

                        clipboard.on('error', function(e) {

                            console.log(e);
                        });


                    }
                },
                error: function (json) {
                    alert("异常错误!");
                    $("#msgShow").html(json.status);
                }
            });
        });

    })
</script>

</body></html>