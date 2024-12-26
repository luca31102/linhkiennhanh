<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_NhanVien extends CI_Model {

	public $variable;

	public function __construct()
	{
		parent::__construct();
		
	}

	public function getById($MaNhanVien){
		$sql = "SELECT * FROM nhanvien WHERE MaNhanVien = ? AND TrangThai = 1";
		$result = $this->db->query($sql, array($MaNhanVien));
		return $result->result_array();
	}


	public function update($HoTen,$TaiKhoan,$MatKhau,$Email,$SoDienThoai,$MaNhanVien){
		$sql = "UPDATE nhanvien SET HoTen = ?, TaiKhoan = ?, MatKhau = ?, Email = ?, SoDienThoai = ? WHERE MaNhanVien = ?";
		$result = $this->db->query($sql, array($HoTen,$TaiKhoan,$MatKhau,$Email,$SoDienThoai,$MaNhanVien));
		return $result;
	}
}

/* End of file Model_NhanVien.php */
/* Location: ./application/models/Model_NhanVien.php */