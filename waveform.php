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

        /* process original dimensions, pdadings etc. */
        $this->setDimensions($options);

        /* use interpolating to make spectrogram more smooth */
        $this->interpolate = isset($options['interpolate']) ? $options['interpolate'] : TRUE;

        /* prepare GD image object */
        $this->createImage();
    }

    private function setDimensions($options)
    {
        /* check if padding first */
        $this->horizontalPadding    = isset($options['horizontalPadding']) ? ($options['horizontalPadding'] / 100) : 0;
        $this->verticalPadding      = isset($options['verticalPadding']) ? ($options['verticalPadding'] / 100) : 0;

        /* save original sizes */
        $this->originalWidth       = $options['width'];
        $this->originalHeight      = $options['height'];

        /* image size */
        $this->width       = $this->originalWidth - ($this->originalWidth * $this->horizontalPadding * 2);
        $this->height      = $this->originalHeight - ($this->originalHeight * $this->verticalPadding * 2);

        /* one side padding */
        $this->diffWidth    = ($this->originalWidth - $this->width) / 2;
        $this->diffHeight   = ($this->originalHeight - $this->height) / 2;
    }

    /**
     * Prepare GD image object
     */
    private function createImage()
    {
        $this->img = imagecreatetruecolor($this->originalWidth, $this->originalHeight);

        /* prepare RGB values of background color */
        list($r, $g, $b) = $this->html2rgb($this->outerColor);
        $backgroundColor = imagecolorallocate($this->img, $r, $g, $b);

        /* fill image with background color */
        imagefill($this->img, 0, 0, $backgroundColor);
    }

    public function dumpWaveformData()
    {
        return array(
            $this->horizontalPadding, $this->verticalPadding,
            $this->originalWidth, $this->originalHeight,
            $this->width, $this->height,
            $this->diffWidth, $this->diffHeight,
            $this->originalData,
            $this->method,    
            $this->data,
            count($this->data)
        );
    }

    /**
     * normalize data and create spectrogram
     */
    public function createWaveform()
    {
        /* process amplitudes values */
        $method             = ($this->interpolate) ? 'interpolateArray' : 'expandArray';
        $this->originalData = $this->data;
        $this->data         = ($this->$method($this->data, $this->width));
        $this->method       = $method;

        /* draw spectrogram on image object */
        $this->createSpectogram();
    }

    /**
     * Draw spectrogram on image object
     */
    private function createSpectogram()
    {
        /* prepare spectrogram color in RGB values */
        list($r, $g, $b) = $this->html2rgb($this->innerColor);

        $dataSize   = count($this->data);
        $middle     = $this->height / 2;

        foreach ($this->data as $i => $item) {
            $t = $this->width / $dataSize;

            $x1 = $t * $i + $this->diffWidth;
            $y1 = round($middle - $middle * $item) + $this->diffHeight;

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