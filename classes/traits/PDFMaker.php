<?php

namespace OFFLINE\Mall\Classes\Traits;

use Barryvdh\DomPDF\Facade\Pdf;
use Cms\Classes\CmsException;
use Cms\Classes\Controller;

trait PDFMaker
{
    /**
     * Generate a PDF document from a partial in the plugins/mallPDF folder.
     *
     * @param string $dir
     * @param array $data
     *
     * @throws CmsException
     * @return \Barryvdh\DomPDF\PDF
     */
    public function makePDFFromDir(string $dir, array $data = [])
    {
        $partial = sprintf('mallPDF/%s/default.htm', $dir);

        return $this->makePDFFromPartial($partial, $data);
    }

    /**
     * Generate a PDF document from a partial.
     *
     * @param string $partial
     * @param array $data
     *
     * @throws CmsException
     * @return \Barryvdh\DomPDF\PDF
     */
    public function makePDFFromPartial(string $partial, array $data = [])
    {
        $controller = Controller::getController() ?? new Controller();

        $contents = $controller->renderPartial($partial, $data);

        return $this->makePDFFromString($contents);
    }

    /**
     * Generate a PDF document from a string.
     *
     * @param string $contents
     *
     * @return \Barryvdh\DomPDF\PDF
     */
    public function makePDFFromString(string $contents)
    {
        return Pdf::loadHTML($contents);
    }
}
