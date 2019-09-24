package main

import (
	"bytes"
	"encoding/base64"
	"encoding/json"
	"flag"
	"fmt"
	"image"
	"image/draw"
	"image/jpeg"
	"image/png"
	"io"
	"log"
	"net/http"
	"os"
	"strconv"
	"strings"

	"github.com/boombuler/barcode"
	"github.com/boombuler/barcode/qr"
	"github.com/makiuchi-d/gozxing"
	"github.com/makiuchi-d/gozxing/qrcode"
)

var port = flag.String("port", "8080", "HTTP Service Port Example: 80")

type QRCODE struct {
	content    string
	size       int
	background string
	icon       string
	ext        string
}

type RespQR struct {
	Code int    `json:"code"`
	Msg  string `json:"msg"`
	Data struct {
		ImgBase     string `json:"img_base"`
		ImgBaseIcon string `json:"img_base_icon"`
		ImgBaseBg   string `json:"img_base_bg"`
		ImgContent  string `json:"img_content"`
	} `json:"data"`
}

func main() {
	// 设置路由,如果访问, 则调用index方法.
	http.HandleFunc("/favicon.ico", func(w http.ResponseWriter, request *http.Request) {
		w.WriteHeader(http.StatusNoContent)
	})
	http.HandleFunc("/create", create)
	http.HandleFunc("/parse", parse)
	err := http.ListenAndServe(":"+*port, nil)
	if err != nil {
		log.Fatal("ListenAndServe: ", err)
	}
}

func create(w http.ResponseWriter, r *http.Request) {
	var err error
	r.ParseForm()
	q := &QRCODE{}
	// 判断内容
	q.content = r.Form.Get("content")
	if len(q.content) == 0 {
		w.WriteHeader(400)
		w.Write([]byte("content cannot be null"))
		return
	}
	// 背景
	q.background = strings.ToUpper(r.Form.Get("bg"))
	if q.background != "ALIPAY" && q.background != "WXPAY" && q.background != "UNION" {
		q.background = ""
	}
	// icon类型
	q.icon = strings.ToUpper(r.Form.Get("icon"))
	if q.icon != "ALIPAY" && q.icon != "WXPAY" && q.icon != "UNION" {
		q.icon = ""
	}
	// 尺寸
	q.size, err = strconv.Atoi(r.Form.Get("size"))
	if q.size < 100 {
		q.size = 250
	}
	q.ext = "png"
	ext := strings.ToUpper(r.Form.Get("ext"))
	if ext == "JPEG" || ext == "JPG" {
		q.ext = "jpg"
	}

	resp := RespQR{Code: 0, Msg: ""}
	resp.Data.ImgContent = q.content

	resp.Data.ImgBase, resp.Data.ImgBaseIcon, resp.Data.ImgBaseBg, err = createQrcode(q)
	if err != nil {
		w.WriteHeader(500)
		resp.Msg = err.Error()
		w.Write([]byte(err.Error()))
		return
	}
	res, err := json.Marshal(resp)
	if err != nil {
		w.WriteHeader(500)
		w.Write([]byte(err.Error()))
		return
	}
	w.Header().Set("Content-Type", "application/json;charset=utf-8")
	w.Write(res)
}

func createQrcode(q *QRCODE) (string, string, string, error) {
	qrImg, _ := qr.Encode(q.content, qr.M, qr.Auto) // Create the barcode
	qrImg, _ = barcode.Scale(qrImg, q.size, q.size) // Scale the barcode to 200x200 pixels

	emptyBuff := bytes.NewBuffer(nil) // 开辟一个新的空buff缓冲区
	withNothing := ""
	withIcon := ""
	withBg := ""
	withHead := "data:image/png;base64,"
	if q.ext == "jpg" {
		withHead = "data:image/jpeg;base64,"
	}

	imageEncode(q, emptyBuff, qrImg)
	dst := make([]byte, 50000) // 开辟存储空间
	// 讲二维码转成base64
	base64.StdEncoding.Encode(dst, emptyBuff.Bytes())        // buff转成base64
	withNothing = withHead + string(bytes.Trim(dst, "\x00")) // 输出图片base64(type = []byte)

	// 二维码画布
	bgCanvas := &image.RGBA{}
	// 添加qrcode中间的icon

	if len(q.icon) > 0 {
		fileIco, err := os.Open("img/" + q.icon + "_QR_LOGO.png")
		if err != nil {
			fmt.Println(err.Error())
			return "", "", "", err
		}
		imgIco, err := png.Decode(fileIco)
		if err != nil {
			fmt.Println(err.Error())
			return "", "", "", err
		}
		fileIco.Close()
		// 计算源图的中央大小
		coordinate := (q.size - imgIco.Bounds().Dx()) / 2
		offset := image.Pt(coordinate, coordinate)
		bg := qrImg.Bounds()
		bgCanvas = image.NewRGBA(bg)
		draw.Draw(bgCanvas, bg, qrImg, image.Point{}, draw.Src)
		draw.Draw(bgCanvas, imgIco.Bounds().Add(offset), imgIco, image.Point{}, draw.Over)
		emptyBuff := bytes.NewBuffer(nil)
		imageEncode(q, emptyBuff, bgCanvas)
		base64.StdEncoding.Encode(dst, emptyBuff.Bytes())     // buff转成base64
		withIcon = withHead + string(bytes.Trim(dst, "\x00")) // 输出图片base64(type = []byte)
	}

	if len(q.background) > 0 {
		fileBg, _ := os.Open("img/" + q.background + "_QR_BG.png")
		imgBg, _ := png.Decode(fileBg)
		fileBg.Close()
		bg := imgBg.Bounds()
		offset := image.Pt(60, 90)
		newBgCanvas := image.NewRGBA(bg)
		draw.Draw(newBgCanvas, bg, imgBg, image.Point{}, draw.Src)
		draw.Draw(newBgCanvas, bgCanvas.Bounds().Add(offset), bgCanvas, image.Point{}, draw.Over)
		emptyBuff := bytes.NewBuffer(nil)
		imageEncode(q, emptyBuff, newBgCanvas)
		base64.StdEncoding.Encode(dst, emptyBuff.Bytes())   // buff转成base64
		withBg = withHead + string(bytes.Trim(dst, "\x00")) // 输出图片base64(type = []byte)
	}

	return withNothing, withIcon, withBg, nil
}

func imageEncode(q *QRCODE, w io.Writer, m image.Image) error {
	//imgw, _ := os.Create(strconv.Itoa(rand.Int())+".png")
	//png.Encode(imgw, m)
	//defer imgw.Close()

	if q.ext == "png" {
		return png.Encode(w, m)
	}
	return jpeg.Encode(w, m, nil)
}

func imageDecode(q *QRCODE, r io.Reader) (image.Image, error) {
	if q.ext == "png" {
		return png.Decode(r)
	}
	return jpeg.Decode(r)
}

// ********************************************************* TODO 解析本地二维码内容 ?file=tmp/file.jpg&del=true *********************************************************
func parse(w http.ResponseWriter, r *http.Request) {
	var err error
	r.ParseForm()
	localFile := r.Form.Get("file")
	if len(localFile) == 0 {
		w.WriteHeader(400)
		w.Write([]byte("filepath cannot be null"))
		return
	}

	resp := RespQR{Code: 0, Msg: ""}
	resp.Data.ImgContent, err = DecodeFile(localFile)
	if err != nil {
		w.WriteHeader(400)
		w.Write([]byte(err.Error()))
	}
	w.Header().Set("Content-Type", "application/json;charset=utf-8")
	res, err := json.Marshal(resp)
	if err != nil {
		w.WriteHeader(500)
		w.Write([]byte(err.Error()))
		return
	}
	isDeleteFile := r.Form.Get("del")
	if len(isDeleteFile) > 0 {
		go os.Remove(localFile) // 异步删除文件
	}
	w.Header().Set("Content-Type", "application/json;charset=utf-8")
	w.Write(res)
}

func DecodeFile(fi string) (string, error) {
	file, err := os.Open(fi)
	if err != nil {
		return "", err
	}
	img, _, err := image.Decode(file)
	if err != nil {
		return "", err
	}
	// prepare BinaryBitmap
	bmp, err := gozxing.NewBinaryBitmapFromImage(img)
	if err != nil {
		return "", err
	}
	// decode image
	result, err := qrcode.NewQRCodeReader().Decode(bmp, nil)
	if err != nil {
		return "", err
	}
	return result.String(), nil
}
