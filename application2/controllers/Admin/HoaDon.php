<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class HoaDon extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		if(!$this->session->has_userdata('taikhoan')){
			return redirect(base_url('admin/dang-nhap/'));
		}

		$this->load->model('Admin/Model_HoaDon');
		$this->load->model('Admin/Model_CauHinh');
	}

	public function index()
	{
		$totalRecords = $this->Model_HoaDon->checkNumber();
		$recordsPerPage = 10;
		$totalPages = ceil($totalRecords / $recordsPerPage); 

		$data['totalPages'] = $totalPages;
		$data['list'] = $this->Model_HoaDon->getAll();
		$data['title'] = "Danh sách hóa đơn";
		return $this->load->view('Admin/View_HoaDon', $data);
	}

	public function page($trang){
		$data['title'] = "Danh sách hóa đơn";
		$totalRecords = $this->Model_HoaDon->checkNumber();
		$recordsPerPage = 10;
		$totalPages = ceil($totalRecords / $recordsPerPage); 

		if($trang < 1){
			return redirect(base_url('admin/hoa-don/'));
		}

		if($trang > $totalPages){
			return redirect(base_url('admin/hoa-don/'));
		}

		$start = ($trang - 1) * $recordsPerPage;


		if($start == 0){
			$data['totalPages'] = $totalPages;
			$data['list'] = $this->Model_HoaDon->getAll();
			return $this->load->view('Admin/View_HoaDon', $data);
		}else{
			$data['totalPages'] = $totalPages;
			$data['list'] = $this->Model_HoaDon->getAll($start);
			return $this->load->view('Admin/View_HoaDon', $data);
		}
	}

	public function view($mahoadon){
		if(count($this->Model_HoaDon->getById($mahoadon)) <= 0){
			$this->session->set_flashdata('error', 'Hóa đơn không tồn tại!');
			return redirect(base_url('admin/hoa-don/'));
		}
		$data['config'] = $this->Model_CauHinh->getAll();
		$data['list'] = $this->Model_HoaDon->getDetailById($mahoadon);
		$data['detail'] = $this->Model_HoaDon->getById($mahoadon);
		$data['title'] = "Chi tiết hóa đơn";
		return $this->load->view('Admin/View_XemHoaDon', $data);
	}

	public function pay($mahoadon)
	{
		if(count($this->Model_HoaDon->getById($mahoadon)) <= 0){
			$this->session->set_flashdata('error', 'Hóa đơn không tồn tại!');
			return redirect(base_url('admin/hoa-don/'));
		}

		$detail = $this->Model_HoaDon->getById($mahoadon);

		if(($detail[0]['ThanhToan'] != 1) && ($detail[0]['TrangThai'] != 0) && ($detail[0]['TrangThai'] != 4)){
			$this->Model_HoaDon->updatePay($mahoadon);
			$this->session->set_flashdata('success', 'Xác nhận thanh toán thành công!');
			return redirect(base_url('admin/hoa-don/'.$mahoadon.'/xem/'));
		}else{
			$this->session->set_flashdata('error', 'Không được phép thực hiện!');
			return redirect(base_url('admin/hoa-don/'.$mahoadon.'/xem/'));
		}
	}


	public function cancel($mahoadon){
		if(count($this->Model_HoaDon->getById($mahoadon)) <= 0){
			$this->session->set_flashdata('error', 'Hóa đơn không tồn tại!');
			return redirect(base_url('admin/hoa-don/'));
		}

		$detail = $this->Model_HoaDon->getById($mahoadon);

		if(($detail[0]['TrangThai'] <= 2) && ($detail[0]['TrangThai'] != 0) && ($detail[0]['TrangThai'] != 4)){
			$this->Model_HoaDon->cancel($mahoadon);

			$detailOrder = $this->Model_HoaDon->getDetailById($mahoadon);

			foreach ($detailOrder as $key => $value) {
				$soluongmoi = $this->Model_HoaDon->getProductById($value['MaSanPham'])[0]['SoLuong'] + $value['SoLuong'];
				$this->Model_HoaDon->updateNumberProduct($soluongmoi,$value['MaSanPham']);
			}

			$this->session->set_flashdata('success', 'Hủy đơn hàng thành công!');
			return redirect(base_url('admin/hoa-don/'.$mahoadon.'/xem/'));
		}else{
			$this->session->set_flashdata('error', 'Không được phép thực hiện!');
			return redirect(base_url('admin/hoa-don/'.$mahoadon.'/xem/'));
		}
	}


	public function status($mahoadon){
		if(count($this->Model_HoaDon->getById($mahoadon)) <= 0){
			$this->session->set_flashdata('error', 'Hóa đơn không tồn tại!');
			return redirect(base_url('admin/hoa-don/'));
		}

		$detail = $this->Model_HoaDon->getById($mahoadon);

		if(($detail[0]['TrangThai'] == 1) || ($detail[0]['TrangThai'] == 2) || ($detail[0]['TrangThai'] == 3)){
			$status = $detail[0]['TrangThai'] + 1;

			if($status == 4){
				$detailOrder = $this->Model_HoaDon->getDetailById($mahoadon);

				foreach ($detailOrder as $key => $value) {
					$soluongmoi = $this->Model_HoaDon->getProductById($value['MaSanPham'])[0]['SoLuong'] + $value['SoLuong'];
					$this->Model_HoaDon->updateNumberProduct($soluongmoi,$value['MaSanPham']);
				}
			}

			$this->Model_HoaDon->status($status, $mahoadon);
			$this->session->set_flashdata('success', 'Cập nhật trạng thái đơn hàng thành công!');
			return redirect(base_url('admin/hoa-don/'.$mahoadon.'/xem/'));
		}else{
			$this->session->set_flashdata('error', 'Không được phép thực hiện!');
			return redirect(base_url('admin/hoa-don/'.$mahoadon.'/xem/'));
		}
	}

	public function search()
	{
		if(!isset($_POST['madonhang']) && !isset($_POST['thanhtoan']) && !isset($_POST['trangthai'])){
			return redirect(base_url('admin/hoa-don/'));
		}

		$madonhang = $this->input->post('madonhang');
		$trangthai = $this->input->post('trangthai');
		$thanhtoan = $this->input->post('thanhtoan');

		if(empty($madonhang) && empty($trangthai) && empty($thanhtoan)){
			return redirect(base_url('admin/hoa-don/'));
		}

		
		$data['post'] = array(
			'madonhang' => $madonhang,
			'trangthai' => $trangthai,
			'thanhtoan' => $thanhtoan,
		);

		if($thanhtoan == -1){
			$thanhtoan = 0;
		}

		if($trangthai == -1){
			$trangthai = 0;
		}

		if(empty($_POST['thanhtoan']) || !isset($_POST['thanhtoan'])){
			$thanhtoan = -1;
		}

		if(empty($_POST['trangthai']) || !isset($_POST['trangthai'])){
			$trangthai = -1;
		}


		$totalRecords = $this->Model_HoaDon->checkNumberSearch($madonhang,$thanhtoan,$trangthai);
		$recordsPerPage = 10;
		$totalPages = ceil($totalRecords / $recordsPerPage); 

		$data['totalPages'] = $totalPages;
		$data['list'] = $this->Model_HoaDon->search($madonhang,$thanhtoan,$trangthai);
		$data['title'] = "Danh sách hóa đơn";
		return $this->load->view('Admin/View_HoaDonTimKiem', $data);

	}


	public function pageSearch($trang){
		if(!isset($_GET['madonhang']) && !isset($_GET['thanhtoan']) && !isset($_GET['trangthai'])){
			return redirect(base_url('admin/hoa-don/'));
		}

		$madonhang = $this->input->get('madonhang');
		$trangthai = $this->input->get('trangthai');
		$thanhtoan = $this->input->get('thanhtoan');

		if(empty($madonhang) && empty($trangthai) && empty($thanhtoan)){
			return redirect(base_url('admin/hoa-don/'));
		}

		
		$data['post'] = array(
			'madonhang' => $madonhang,
			'trangthai' => $trangthai,
			'thanhtoan' => $thanhtoan,
		);

		if($thanhtoan == -1){
			$thanhtoan = 0;
		}

		if($trangthai == -1){
			$trangthai = 0;
		}

		if(empty($_GET['thanhtoan']) || !isset($_GET['thanhtoan'])){
			$thanhtoan = -1;
		}

		if(empty($_GET['trangthai']) || !isset($_GET['trangthai'])){
			$trangthai = -1;
		}


		$data['title'] = "Danh sách hóa đơn";
		$totalRecords = $this->Model_HoaDon->checkNumberSearch($madonhang,$thanhtoan,$trangthai);
		$recordsPerPage = 10;
		$totalPages = ceil($totalRecords / $recordsPerPage); 

		if($trang < 1){
			return redirect(base_url('admin/hoa-don/'));
		}

		if($trang > $totalPages){
			return redirect(base_url('admin/hoa-don/'));
		}

		$start = ($trang - 1) * $recordsPerPage;


		if($start == 0){
			$data['totalPages'] = $totalPages;
			$data['list'] = $this->Model_HoaDon->search($madonhang,$thanhtoan,$trangthai);
			return $this->load->view('Admin/View_HoaDonTimKiem', $data);
		}else{
			$data['totalPages'] = $totalPages;
			$data['list'] = $this->Model_HoaDon->search($madonhang,$thanhtoan,$trangthai,$start);
			return $this->load->view('Admin/View_HoaDonTimKiem', $data);
		}
	}

	public function type(){
		if(!isset($_GET['type'])){
			return redirect(base_url('admin/hoa-don/'));
		}

		$type = $this->input->get('type');

		if(($type != "thang") && ($type != "tuan")){
			return redirect(base_url('admin/hoa-don/'));
		}

		$data['post'] = array(
			"type" => $type
		);

		$totalRecords = $this->Model_HoaDon->checkNumberType($type);
		$recordsPerPage = 10;
		$totalPages = ceil($totalRecords / $recordsPerPage); 

		$data['totalPages'] = $totalPages;
		$data['list'] = $this->Model_HoaDon->getType($type);
		$data['title'] = "Danh sách hóa đơn";
		return $this->load->view('Admin/View_HoaDonThongKe', $data);
	}

	public function pageType($trang){
		if(!isset($_GET['type'])){
			return redirect(base_url('admin/hoa-don/'));
		}

		$type = $this->input->get('type');

		if(($type != "thang") && ($type != "tuan")){
			return redirect(base_url('admin/hoa-don/'));
		}

		$data['post'] = array(
			"type" => $type
		);

		$data['title'] = "Danh sách hóa đơn";
		$totalRecords = $this->Model_HoaDon->checkNumberType($type);
		$recordsPerPage = 10;
		$totalPages = ceil($totalRecords / $recordsPerPage); 

		if($trang < 1){
			return redirect(base_url('admin/hoa-don/'));
		}

		if($trang > $totalPages){
			return redirect(base_url('admin/hoa-don/'));
		}

		$start = ($trang - 1) * $recordsPerPage;


		if($start == 0){
			$data['totalPages'] = $totalPages;
			$data['list'] = $this->Model_HoaDon->getType($type);
			return $this->load->view('Admin/View_HoaDonThongKe', $data);
		}else{
			$data['totalPages'] = $totalPages;
			$data['list'] = $this->Model_HoaDon->getType($type,$start);
			return $this->load->view('Admin/View_HoaDonThongKe', $data);
		}
	}
}

/* End of file ChuyenMuc.php */
/* Location: ./application/controllers/ChuyenMuc.php */