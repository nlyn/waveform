<?php

/**
 * Class Waveform
 */
class Waveform
{
    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->data        = $options['data'];

        /* spectogram color*/
        $this->innerColor  = $options['innerColor'];

        /* background color */
        $this->outerColor  = $options['outerColor'];

        /* image size */
        $this->width       = $options['width'];
        $this->height      = $options['height'];

        /* use interpolating to make spectrogram more smooth */
        $this->interpolate = isset($options['interpolate']) ? $options['interpolate'] : TRUE;

        /* prepare GD image object */
        $this->createImage();
    }

    /**
     * Prepare GD image object
     */
    private function createImage()
    {
        $this->img = imagecreatetruecolor($this->width, $this->height);

        /* prepare RGB values of background color */
        list($r, $g, $b) = $this->html2rgb($this->outerColor);
        $backgroundColor = imagecolorallocate($this->img, $r, $g, $b);

        /* fill image with background color */
        imagefill($this->img, 0, 0, $backgroundColor);
    }

    /**
     * normalize data and create spectrogram
     */
    public function createWaveform()
    {
        /*  */
        $method     = ($this->interpolate) ? 'interpolateArray' : 'expandArray';
        $this->data = ($this->$method($this->data, $this->width));

        $this->createSpectogram();
    }

    /**
     * Draw spectrogram on image object
     */
    private function createSpectogram()
    {
        /* prepare spectrogram color in RGB values */
        list($r, $g, $b) = $this->html2rgb($this->innerColor);

        /* find vertical center of image */
        $middle = $this->height / 2;

        foreach ($this->data as $i => $item) {
            $t = $this->width / count($this->data);

            $x1 = $t * $i;
            $y1 = round($middle - $middle * $item);

            $x2 = $x1 + $t;
            $y2 = $y1 + round($middle * $item * 2);

            imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, imagecolorallocate($this->img, $r, $g, $b));
        }
    }

    /**
     * @param $before
     * @param $after
     * @param $atPoint
     * @return mixed
     */
    private function linearInterpolate($before, $after, $atPoint)
    {
        return $before + ($after - $before) * $atPoint;
    }

    /**
     * @param $data
     * @param $fitCount
     * @return array
     */
    private function interpolateArray($data, $fitCount)
    {
        $newData      = array();
        $springFactor = ((count($data) - 1) / ($fitCount - 1));
        $newData[0]   = $data[0];

        $i = 1;

        while ($i < $fitCount - 1) {
            $tmp         = $i * $springFactor;
            $before      = round(floor($tmp));
            $after       = round(ceil($tmp));
            $atPoint     = $tmp - $before;
            $newData[$i] = $this->linearInterpolate($data[$before], $data[$after], $atPoint);
            $i++;
        }

        $newData[$fitCount - 1] = $data[count($data) - 1];
        return $newData;
    }

    /**
     * @param $data
     * @param $limit
     * @param int $defaultValue
     * @return array
     */
    private function expandArray($data, $limit, $defaultValue = 0)
    {
        $newData = array();

        if (count($data) > $limit) {
            $newData = array_slice($data, count($data) - $limit, count($data));
        } else {
            for ($i = 0; $i < $limit; $i++) {
                $newData[$i] = $data[$i] || $defaultValue;
            }
        }

        return $newData;
    }

    /**
     * @param $hexColor
     * @return array
     */
    private function html2rgb($hexColor)
    {
        $hexColor = ($hexColor[0] == "#") ? substr($hexColor, 1, 6) : substr($hexColor, 0, 6);

        return array(
            hexdec(substr($hexColor, 0, 2)),
            hexdec(substr($hexColor, 2, 2)),
            hexdec(substr($hexColor, 4, 2))
        );
    }

    /**
     * @param $filename
     */
    public function toImage($filename)
    {
        imagepng($this->img, $filename);
        imagedestroy($this->img);
    }
}

?>