<?php
/**
 * @copyright Copyright (c) 2013-2015 2amigOS! Consulting Group LLC
 * @link http://2amigos.us
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace dosamigos\leaflet\layers;

use dosamigos\leaflet\types\LatLng;
use dosamigos\leaflet\types\LatLngBounds;
use yii\base\InvalidParamException;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * PolyLine is a class for drawing a polygon overlay on the map.
 *
 * @see http://leafletjs.com/reference.html#polyline
 * @package dosamigos\leaflet\layers
 */
class PolyLine extends Layer
{
    use PopupTrait;

    /**
     * @var LatLng[]
     */
    private $_latLngs = [];
    /**
     * @var LatLngBounds
     */
    private $_bounds;

    /**
     * @param array $latLngs
     *
     * @throws \yii\base\InvalidParamException
     */
    public function setLatLngs($latLngs)
    {
        foreach ((array)$latLngs as $latLng) {
            if (!($latLng instanceof LatLng)) {
                throw new InvalidParamException("Wrong parameter. All items should be of type LatLng.");
            }
        }
        $this->setBounds();
        $this->_latLngs = $latLngs;
    }

    /**
     * @return \dosamigos\leaflet\types\LatLng[]
     */
    public function getLatLngs()
    {
        return $this->_latLngs;
    }

    /**
     * Returns the latLngs as array objects
     * @return array
     */
    public function getLatLngstoArray()
    {
        $latLngs = [];
        foreach ($this->getLatLngs() as $latLng) {
            $latLngs[] = $latLng->toArray();
        }
        return $latLngs;
    }

    /**
     * Returns the LatLngBounds of the polyline.
     * @return LatLngBounds
     */
    public function getBounds()
    {
        return $this->_bounds;
    }

    /**
     * Sets bounds after initialization of the [[LatLng]] objects that compound the polyline.
     */
    protected function setBounds()
    {
        foreach ($this->getLatLngs() as $latLng) {
            if (empty($this->_bounds)) {
                $this->_bounds = new LatLngBounds(['southWest' => $latLng, 'northEast' => $latLng]);
            } else {
                $this->_bounds->getSouthWest()->lat = min($latLng->lat, $this->_bounds->getSouthWest()->lat);
                $this->_bounds->getSouthWest()->lng = min($latLng->lng, $this->_bounds->getSouthWest()->lng);

                $this->_bounds->getNorthEast()->lat = min($latLng->lat, $this->_bounds->getNorthEast()->lat);
                $this->_bounds->getNorthEast()->lng = min($latLng->lng, $this->_bounds->getNorthEast()->lng);
            }
        }
    }

    /**
     * Returns the javascript ready code for the object to render
     * @return string
     */
    function encode()
    {
        $latLngs = Json::encode($this->getLatLngstoArray());
        $options = $this->getOptions();
        $name = $this->name;
        $map = $this->map;
        $js = $this->bindPopupContent("L.polyline($latLngs, $options)") . ($map !== null ? ".addTo($map);" : "");
        if (!empty($name)) {
            $js = "var $name = $js" . ($map !== null ? "" : ";");
        }

        return new JsExpression($js);
    }

} 