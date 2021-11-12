<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Airport - Việt Nam</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css" />
    <script src="https://openlayers.org/en/v4.6.5/build/ol.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" type="text/javascript"></script>
    <style>
        .frame_map{
            width: 81%;
            float: left;
        }
        .form{
            height: 98vh;
            width: 18.9%;
            float: left;
            border-left:0.1px solid black;
            height: 98.1vh;
        }
        .btn{
          position: relative;
          background: none ;
          border:2px solid #000 ;
          font-family: sans-serif;
          cursor: pointer;
          transition: color 0.4s linear;
        }

        .btn:hover{
          color: #c4821f ;
          font-weight: bold;
        }

        .ol-popup {
            position: absolute;
            background-color: white;
            -webkit-filter: drop-shadow(0 1px 4px rgba(0, 0, 0, 0.2));
            filter: drop-shadow(0 1px 4px rgba(0, 0, 0, 0.2));
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #cccccc;
            bottom: 12px;
            left: -50px;
            min-width: 180px;
        }

        .ol-popup:after,
        .ol-popup:before {
            top: 100%;
            border: solid transparent;
            content: " ";
            height: 0;
            width: 0;
            position: absolute;
            pointer-events: none;
        }

        .ol-popup:after {
            border-top-color: white;
            border-width: 10px;
            left: 48px;
            margin-left: -10px;
        }

        .ol-popup:before {
            border-top-color: #cccccc;
            border-width: 11px;
            left: 48px;
            margin-left: -11px;
        }

        .ol-popup-closer {a
            text-decoration: none !important; 
            position: absolute;
            top: 2px;
            right: 8px;
            color:red;
        }

        .ol-popup-closer:after {
            content: "✖";
        }
    </style>
</head>

<body onload="initialize_map();">
                <div id="popup" class="ol-popup">
                    <a href="#" id="popup-closer" class="ol-popup-closer" style="text-decoration: none"></a>
                    <div id="popup-content"></div>
                </div>
            <div class="container" style="border:0.1px solid black;height: 98.1vh;">
                <div class="frame_map" >
                    <div id="map" class="map" style="height:98.1vh"></div>
                </div>
                <div class="form">
                    <h3 class="text-center" style="color:#3289a8;font-family:Time New Roman;text-align:center">AIRPORT - VIETNAM</h3>
                    <div class="group-form" style="padding-left:10px; padding-top:10px; border:1px solid black;margin:7px;">
                        <input type="textinput" id="name_airport" placeholder="Nhập tên sân bay">
                        <button type="button" id="btnSeach" class="btn btn1 ">Search</button>
                        <br>
                        <br>
                        <input onclick="onchecklocation();" type="checkbox" id="location" name="layer" value="location">Locations<br />
                        <br>
                        <button id="btnRest" class =" btn btn2 ">Load</button>
                        <br>
                        <br>
                    </div>
                    <div class="detail_search" id="form-search">
                        <div class="detait_header" style="text-align:center;color:#d91111">
                            <span >Kết quả tìm kiếm </span>
                        </div>
                            <div class="group-form" style="padding-left:10px; padding-top:10px; border:1px solid black;margin:7px;">
                                <div class="detail_body" id="detail_body">
                            </div>
                        </div>
                    </div>
                </div>
                <div id="info"></div>
            </div>
    <?php include 'CMR_pgsqlAPI.php' ?>

    <script>
        var format = 'image/png';
        var map;
        var minX = 103.007827758789;
        var minY = 8.73183059692383;
        var maxX = 109.333709716797;
        var maxY = 21.397481918335;
        var cenX = (minX + maxX) / 2;
        var cenY = (minY + maxY) / 2;
        var mapLat = cenY;
        var mapLng = cenX;
        var mapDefaultZoom = 6;

        var layer_location;
        var vectorLayer;
        var styleFunction;
        var styles;
        var container = document.getElementById('popup');
        var closer = document.getElementById('popup-closer');
        var name_airport = document.getElementById("name_airport");
        var chklocation = document.getElementById("location");
        var information='';
        /**
         * Create an overlay to anchor the popup to the map.
         */
        var overlay = new ol.Overlay( /** @type {olx.OverlayOptions} */ ({
            element: container,
            autoPan: true,
            autoPanAnimation: {
                duration: 250
            }
        }));
        closer.onclick = function() {
            overlay.setPosition(undefined);
            closer.blur();
            return false;
        };
        function handleOnCheck(id, layer) {
            if (document.getElementById(id).checked) {
                information = document.getElementById(id).value;
                map.addLayer(layer)
                vectorLayer = new ol.layer.Vector({});
                map.addLayer(vectorLayer);
            } else {
                map.removeLayer(layer);
                map.removeLayer(vectorLayer);
            }
        }
        // function myFunction() {
        //     var popup = document.getElementById("popup");
        //     popup.classList.toggle("show");
        // }
        function onchecklocation() {
            handleOnCheck('location', layer_location);

        }

        function initialize_map() {

            //khai bao ban do nen Openstreetmap
            layerBG = new ol.layer.Tile({
                source: new ol.source.OSM({})
            });

            
            layer_location = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/example/wms?',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.1',
                        STYLES: '',
                        LAYERS: 'airport',
                    }
                })

            });


            // Them ban do nen openstreetmap
            var viewMap = new ol.View({
                center: ol.proj.fromLonLat([mapLng, mapLat]),
                zoom: mapDefaultZoom
            });
            map = new ol.Map({
                target: "map",
                layers: [layerBG],
                view: viewMap,
                overlays: [overlay], //them khai bao overlays
            });
            styles = {
                'Point': new ol.style.Style({
                    fill: new ol.style.Fill({
                color: 'orange'
                }),
                    stroke: new ol.style.Stroke({
                        color: 'yellow',
                        width: 2
                    })
                })
            };
            styleFunction = function(feature) {
                return styles[feature.getGeometry().getType()];
            };
            var stylePoint = new ol.style.Style({
                image: new ol.style.Icon({
                    anchor: [0.5, 0.5],
                    anchorXUnits: "fraction",
                    anchorYUnits: "fraction",
                    src: "http://localhost/WebGis/Yellow_dot.svg"
                })
            });
            vectorLayer = new ol.layer.Vector({
                style: styleFunction
            });
            function createJsonObj(result) {
                var geojsonObject = '{' +
                    '"type": "FeatureCollection",' +
                    '"crs": {' +
                    '"type": "name",' +
                    '"properties": {' +
                    '"name": "EPSG:4326"' +
                    '}' +
                    '},' +
                    '"features": [{' +
                    '"type": "Feature",' +
                    '"geometry": ' + result +
                    '}]' +
                    '}';
                return geojsonObject;
            }
            function highLightGeoJsonObj(paObjJson) {
                var vectorSource = new ol.source.Vector({
                    features: (new ol.format.GeoJSON()).readFeatures(paObjJson, {
                        dataProjection: 'EPSG:4326',
                        featureProjection: 'EPSG:3857'
                    })
                });
                vectorLayer.setSource(vectorSource);
            }

            function highLightObj(result) {
                var strObjJson = createJsonObj(result);
                var objJson = JSON.parse(strObjJson);
                highLightGeoJsonObj(objJson);
            }

            function displayObjInfo(result, coordinate) {
                $("#popup-content").html(result);
                overlay.setPosition(coordinate);

            }
            function showinfo(result) {
            $("#detail_body").html(result);
            }
            function hiddenPopup() {
                document.getElementById("popup").style.visibility = "hidden";
            }
            function showPopup() {
                document.getElementById("popup").style.visibility = "visible";
            }
            
            map.addLayer(vectorLayer);
            var buttonReset = document.getElementById("btnRest").addEventListener("click", () => {
                location.reload();
            })
            //hidden form search
            document.getElementById("form-search").style.visibility = "hidden";

            //event search information
            var button = document.getElementById("btnSeach").addEventListener("click",
                () => {
                    hiddenPopup();
                    document.getElementById("form-search").style.visibility = "visible";
                    if (information == '')
                    {
                        alert('Vui lòng chọn bản đồ');
                    }
                    if (information == "location"){
                        vectorLayer.setStyle(stylePoint);
                        if(name_airport.value !=''){
                            $.ajax({
                                type: "POST",
                                url: "CMR_pgsqlAPI.php",
                                data: {
                                    functionsearch: 'searchInforAirport',
                                    search: name_airport.value
                                },
                                success: function(result, status, erro) {

                                    if (result == 'null')
                                        // alert("không tìm thấy đối tượng");
                                        return;
                                    else
                                        showinfo(result);
                                },
                                error: function(req, status, error) {
                                    alert(req + " " + status + " " + error);
                                }
                            });
                            $.ajax({
                            type: "POST",
                            url: "CMR_pgsqlAPI.php",
                            data: {
                                functionsearch: 'searchIndexAirport',
                                search: name_airport.value
                            },
                                success: function(result, status, erro) {

                                if (result == 'null')
                                    alert("không tìm thấy đối tượng");
                                else
                                    highLightObj(result);
                            },
                            error: function(req, status, error) {
                                alert(req + " " + status + " " + error);
                                }
                            })
                        }else{
                            alert("Nhập dữ liệu tìm kiếm");
                        }
                    }
                });
                
            //event click show information and index of Airport
            map.on('singleclick', function(evt) {
                var lonlat = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
                var lon = lonlat[0];
                var lat = lonlat[1];
                var myPoint = 'POINT(' + lon + ' ' + lat + ')';
                // $newrs='';
                // showinfo($newrs);
                document.getElementById("form-search").style.visibility = "hidden";
                document.getElementById("name_airport").value = "";
                showPopup();
                if (information == "location") {
                    
                    vectorLayer.setStyle(stylePoint);
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoLocationToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });

                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getGeoEagleToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            highLightObj(result);
                            // alert(result);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }
            });
        };
    </script>
</body>

</html>
