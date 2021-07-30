<?php

namespace CS\Hauora;

use SilverStripe\Assets\Image;
use SilverStripe\Forms\TextField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DataObject;

/**
 * @package KBSlideShow
 */

/**
 * The slide model class that contains all fields and configuration
 * related to individual slides.
 */
class KBSlide extends DataObject
{
    private static $table_name = "KBSlide";

    /**
     * Add fields here to easily extend functionality of your slides.
     * Remember to reflect any changes in {@Link KBSlide::getCMSFields()}.
     *
     * @var array
     */
    private static $db = array(

        'Title' => 'Varchar',
        'Text' => 'Text',
        'SlideLink' => 'Text',
        'ShowSlideText' => 'Boolean',
        'SortOrder' => 'Int', # Used by SortableGridField module, if installed

    );

    /**
     * KBSlideshow is an extension of Page, hence the has_one relation
     * with Page rather than {@Link KBSlideshow}.
     *
     * @var array
     */
    private static $has_one = array(

        'Image' => Image::class,
        'Page' => 'Page',

    );

    /**
     * Fields shown in the Page CMS GridField.
     *
     * @var array
     */
    private static $summary_fields = array(

        'GridThumbnail' => '',
        'Title' => 'Title',
        'Text' => 'Text',
        'SlideLink' => 'Link',

    );

    /**
     * Description labels for the fields shown in the Page CMS GridField.
     *
     * @var array
     */
    private static $field_names = array(

        'Title' => 'Title',
        'Text' => 'Text',
        'SlideLink' => 'Link',

    );

    /**
     * Thumbnail function that falls back on a string indicating that an
     * image doesn't exist.
     *
     * @return Image|String
     */
    public function getGridThumbnail()
    {

        if ($this->Image()->exists()) {
            return $this->Image()->SetWidth(100);
        }

        return _t(KBSlide::class . '.NoImageExists', '(no image)');

    }

    /**
     * Creates the CMS interface for managing slides.
     * Remember to update this function to correspond with $db and $has_one
     * if you are extending the functionality of slides.
     *
     * @return FieldList
     */
    public function getCMSFields()
    {

        $title = TextField::create(
            'Title',
            _t(KBSlideshow::class . '.Title', 'Title')
        );

        $text = TextField::create(
            'Text',
            _t(KBSlideshow::class . '.Text', 'Text')
        );

        $link = TextField::create(
            'SlideLink',
            _t(KBSlideshow::class . '.SlideLink', 'Link')
        );

        $upload = UploadField::create(
            Image::class,
            _t(KBSlideshow::class . '.Image', Image::class)
        );

        $upload
            ->setAllowedMaxFileNumber(1)
            ->setRightTitle(_t(KBSlide::class . '.ImageHelp', 'Upload an image that will represent this slide.'));
        $upload
            ->getValidator()
            ->setAllowedExtensions(array('jpg', 'png', 'gif', 'jpeg'));

        $fields = FieldList::create(
            array(

                $title,
                $text,
                CheckboxField::create('ShowSlideText', 'Show Slide Text'),
                $link,
                $upload,

            )
        );

        # Allow for manual input sorting if module:SortableGridField isn't installed
        if (!class_exists('GridFieldSortableRows')) {
            $fields->push(NumericField::create(
                'SortOrder',
                _t(KBSlideshow::class . '.SortOrder', 'Sort order')
            )->setRightTitle(_t(KBSlideshow::class . '.SortOrderHelp', 'A higher number gives the slide a higher priority'))
            );
        }

        return $fields;
    }

    /**
     * Returns an url to the current slides image, cropped accordingly if defined.
     * Returns false if no Image exists.
     *
     * @return bool|string
     */
    public function KBImage()
    {

        if ($this->Image()) {

            $img = $this->Image();
            $w = $this->Page()->Width;
            $h = $this->Page()->Height;

            if ($w && $h && $w != 0 && $h != 0) {
                return $img->CroppedImage($w, $h)->URL;
            }

            if ($w && $w != 0) {
                return $img->SetWidth($w)->URL;
            }

            if ($h && $h != 0) {
                return $img->SetHeight($h)->URL;
            }

            return $img->URL;

        }

        return false;

    }

}
