<?php

namespace BITPAY\Utils;
// 二维码合成
class QRCodeMerge {
	public function getQRCode($content, $bgFilePath = '', $iconFilePath = '', $edgeSize, $dstX, $dstY) {
		// 引入二维码功能
		$pointSize = 10;
		$level     = 'L';
		$edgeWidth = 2;
		$oldQr     = QRcode::pngRawImage($content, FALSE, $level, $pointSize, $edgeWidth);
		// 将生成的二维码进行缩放
		$newQr      = imagecreatetruecolor($edgeSize, $edgeSize); // 新的画布
		$qrEdgeSize = imagesx($oldQr);  // 原图边长
		imagecopyresampled($newQr, $oldQr, 0, 0, 0, 0, $edgeSize, $edgeSize, $qrEdgeSize, $qrEdgeSize);
		ImageDestroy($oldQr); // 删除原图
		if ($iconFilePath) {
			// 把图标放进二维码中
			$ico = imagecreatefromstring(file_get_contents($iconFilePath));
			// 计算ico大小
			$icoSize  = imagesx($ico);
			$position = ($edgeSize - $icoSize) / 2;
			imagecopyresampled($newQr, $ico, $position, $position, 0, 0, $icoSize, $icoSize, $icoSize, $icoSize); // mixed picture
			ImageDestroy($ico);  // 销毁原图
		}
		// 把二维码放入背景, 如果没, 创建一个正方形的图片
		$bg = $bgFilePath ? imagecreatefromstring(file_get_contents($bgFilePath)) : imagecreatetruecolor($edgeSize, $edgeSize);
		imagecopyresampled($bg, $newQr, $dstX, $dstY, 0, 0, $edgeSize, $edgeSize, $edgeSize, $edgeSize); // mixed picture
		return $bg;
	}

	public function outputQRCodePng($content, $bgFilePath = '', $iconFilePath = '', $edgeSize = 250, $dstX = 60, $dstY = 107) {
		$img = $this->getQRCode($content, $bgFilePath, $iconFilePath, $edgeSize, $dstX, $dstY);
		ob_end_clean(); // 把影响图片的因素去除
		Header("Content-type: image/png");
		ImagePng($img);
		ImageDestroy($img);
	}

	public function outputQRCodeBase64($content, $bgFilePath = '', $iconFilePath = '', $edgeSize = 250, $dstX = 60, $dstY = 107) {
		$img = $this->getQRCode($content, $bgFilePath, $iconFilePath, $edgeSize, $dstX, $dstY);
		ob_start();
		ImagePng($img);
		$code = ob_get_clean();
		ImageDestroy($img);
		return 'data:image/png;base64,' . base64_encode($code);
	}

	public function saveQRCode($content, $bgFilePath = '', $iconFilePath = '', $edgeSize = 250, $dstX = 60, $dstY = 107, $filePath) {
		$img = $this->getQRCode($content, $bgFilePath, $iconFilePath, $edgeSize, $dstX, $dstY);
		ImagePng($img, $filePath);
	}
}
