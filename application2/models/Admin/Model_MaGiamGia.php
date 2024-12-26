<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_MaGiamGia extends CI_Model {

	public $variable;

	public function __construct()
	{
		parent::__construct();
		
	}

	public function add($code,$soluong,$dasudung,$giatrigiam,$thoigian){
		$data = array(
	        "Code" => $code,
	        "SoLuong" => $soluong,
	        "DaSuDung" => $dasudung,
	        "GiaTriGiam" => $giatrigiam,
	        "ThoiGian" => $thoigian
	    );

	    $this->db->insert('magiamgia', $data);
	    $lastInsertedId = $this->db->insert_id();

	    return $lastInsertedId;
	}

	public function checkNumber()
	{
		$sql = "SELECT * FROM magiamgia WHERE TrangThai = 1";
		$result = $this->db->query($sql);
		return $result->num_rows();
	}

	public function getAll($start = 0, $end = 10){
		$sql = "SELECT * FROM magiamgia WHERE TrangThai = 1 ORDER BY MaGiamGia DESC LIMIT ?, ?";
		$result = $this->db->query($sql, array($start, $end));
		return $result->result_array();
	}

	public function getById($MaGiamGia){
		$sql = "SELECT * FROM magiamgia WHERE MaGiamGia = ? AND TrangThai = 1";
		$result = $this->db->query($sql, array($MaGiamGia));
		return $result->result_array();
	}

	public function update($code,$soluong,$giatrigiam,$thoigian,$MaGiamGia){
		$sql = "UPDATE magiamgia SET Code = ?, SoLuong = ?, GiaTriGiam = ?, ThoiGian = ? WHERE MaGiamGia = ?";
		$result = $this->db->query($sql, array($code,$soluong,$giatrigiam,$thoigian,$MaGiamGia));
		return $result;
	}

	public function delete($MaGiamGia){
		$sql = "UPDATE magiamgia SET TrangThai = 0 WHERE MaGiamGia = ?";
		$result = $this->db->query($sql, array($MaGiamGia));
		return $result;
	}

}

/* End of file Model_ChuyenMuc.php */
/* Location: ./application/models/Model_ChuyenMuc.php */