waveform
========

Waveform.js PHP port

Currently it's only quick port. 

Example:
--------

```php
$data 		= array(0.00979614,0.0212708,0.0258484,0.0361633,0.0321045,0.0260925,0.195251,0.118988,0.161713,0.250366,0.230225,0.255249,0.186066,0.182159,0.192719,0.254883,0.250671,0.246674,0.179901,0.13324,0.18573,0.223999,0.163513,0.185913,0.171417,0.217987,0.219543,0.262268,0.207397,0.188019,0.138916,0.169342,0.167053,0.128998,0.130219,0.140411,0.181976,0.147125,0.170227);

$options 	= array(
  'data' => $data, 
  'innerColor' => '#ffffff', 
  'outerColor' => '#f25d55', 
  'width' => 400, 
  'height' => 160
);

$waveform 	= new Waveform($options);
$waveform->createWaveform();
$waveform->toImage('test.png');
```

In plans:
---------
*    Imagick driver handling
*    generating waveforms with alpha channel
*    export to SVG, JPEG (currently only PNG hardcoded)
*    waveforms generating based on Soundcloud API
*    antialiasing