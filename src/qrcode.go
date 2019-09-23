package main

//生成二维码代码
import (
	"os"

	"image/png"
	"log"
	"net/http"
	"path/filepath"
	"strconv"
	"encoding/json"
	"fmt"

	"github.com/boombuler/barcode"
	"github.com/boombuler/barcode/qr"
)

const WALLET_PATH = "/extdata/img/qrcode/"
const INVITE_PATH = "/extdata/img/qrcode/i/"
const FORMATTYPE = ".png"

type QRCODE struct {
	content string
	path    string
	size    int
}

func main() {

	http.HandleFunc("/", wallet)
	http.HandleFunc("/i", invite)
	err := http.ListenAndServe(":8080", nil)
	if err != nil {
		log.Fatal("ListenAndServe: ", err)
	}
}

func wallet(w http.ResponseWriter, r *http.Request) {
	var err error
	r.ParseForm()
	q := &QRCODE{}
	q.content = r.Form.Get("content")
	name := r.Form.Get("name")
	q.size = 200
	size := r.Form.Get("size")
	if len(q.content) == 0 || len(name) == 0 {
		w.WriteHeader(400)
		w.Write([]byte("content or name cannot be null"))
		return
	}
	if len(size) != 0 {
		q.size, err = strconv.Atoi(size)
		if err != nil {
			w.WriteHeader(400)
			w.Write([]byte(err.Error()))
			return
		}
	}
	q.path, _ = filepath.Abs(WALLET_PATH + name + FORMATTYPE)

	if err := createQrcode(q); err != nil {
		w.WriteHeader(500)
		w.Write([]byte(err.Error()))
		return
	}

	resp, err := json.Marshal(map[string]string{"path": q.path})
	if err != nil {
		w.WriteHeader(500)
		w.Write([]byte(err.Error()))
		return
	}
	w.Write(resp)
}


func invite(w http.ResponseWriter, r *http.Request) {
	var err error
	url := "http://market.jpay.cc/i/%s.html"
	r.ParseForm()
	q := &QRCODE{}
	uid := r.Form.Get("uid")
	q.content = fmt.Sprintf(url, uid)
	name := uid
	q.size = 200
	size := r.Form.Get("size")
	if len(q.content) == 0 || len(name) == 0 {
		w.WriteHeader(400)
		w.Write([]byte("content or name cannot be null"))
		return
	}
	if len(size) != 0 {
		q.size, err = strconv.Atoi(size)
		if err != nil {
			w.WriteHeader(400)
			w.Write([]byte(err.Error()))
			return
		}
	}
	q.path, _ = filepath.Abs(INVITE_PATH + name + FORMATTYPE)

	if err := createQrcode(q); err != nil {
		w.WriteHeader(500)
		w.Write([]byte(err.Error()))
		return
	}

	resp, err := json.Marshal(map[string]string{"path": q.path})
	if err != nil {
		w.WriteHeader(500)
		w.Write([]byte(err.Error()))
		return
	}
	w.Write(resp)
}

func createQrcode(q *QRCODE) error {
	if _, err := os.Stat(q.path); err == nil {
		return nil
	}
	// Create the barcode
	qrCode, _ := qr.Encode(q.content, qr.M, qr.Auto)
	// Scale the barcode to 200x200 pixels
	qrCode, _ = barcode.Scale(qrCode, q.size, q.size)
	// create the output file
	dir, err := filepath.Abs(filepath.Dir(q.path))
	if err != nil {
		return err
	}

	d, err := os.Stat(dir)
	if err != nil {
		if !os.IsExist(err) {
			if err := os.Mkdir(dir, 0777); err != nil {
				return err
			}
		}else {
			return err
		}
	}else {
		if !d.IsDir() {
			return fmt.Errorf("file exists and file is not a directory")
		}
	}

	file, err := os.Create(q.path)
	if err != nil {
		return err
	}
	defer file.Close()
	// encode the barcode as png
	png.Encode(file, qrCode)
	fmt.Printf("create qrcode: %s, %s, %d \n", q.content, q.path, q.size)
	return nil
}
