<?php

namespace Fast\SisdikBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Siswa
 *
 * @ORM\Table(name="siswa")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Siswa
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="nomor_urut_persekolah", type="integer", nullable=true)
     */
    private $nomorUrutPersekolah;

    /**
     * @var string
     *
     * @ORM\Column(name="nomor_induk_sistem", type="string", length=45, nullable=true)
     */
    private $nomorIndukSistem;

    /**
     * @var integer
     *
     * @ORM\Column(name="nomor_pendaftaran", type="smallint", nullable=true)
     */
    private $nomorPendaftaran;

    /**
     * @var string
     *
     * @ORM\Column(name="nomor_induk", type="string", length=100, nullable=true)
     */
    private $nomorInduk;

    /**
     * @var string
     *
     * @ORM\Column(name="nama_lengkap", type="string", length=300, nullable=true)
     */
    private $namaLengkap;

    /**
     * @var string
     *
     * @ORM\Column(name="jenis_kelamin", type="string", length=255, nullable=true)
     */
    private $jenisKelamin;

    /**
     * @var string
     *
     * @ORM\Column(name="foto", type="string", length=400, nullable=true)
     */
    private $foto;

    /**
     * @var string
     *
     * @ORM\Column(name="agama", type="string", length=100, nullable=true)
     */
    private $agama;

    /**
     * @var string
     *
     * @ORM\Column(name="tempat_lahir", type="string", length=400, nullable=true)
     */
    private $tempatLahir;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tanggal_lahir", type="date", nullable=true)
     */
    private $tanggalLahir;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="nama_panggilan", type="string", length=100, nullable=true)
     */
    private $namaPanggilan;

    /**
     * @var string
     *
     * @ORM\Column(name="kewarganegaraan", type="string", length=200, nullable=true)
     */
    private $kewarganegaraan;

    /**
     * @var integer
     *
     * @ORM\Column(name="anak_ke", type="integer", nullable=true)
     */
    private $anakKe;

    /**
     * @var integer
     *
     * @ORM\Column(name="jumlah_saudarakandung", type="integer", nullable=true)
     */
    private $jumlahSaudarakandung;

    /**
     * @var integer
     *
     * @ORM\Column(name="jumlah_saudaratiri", type="integer", nullable=true)
     */
    private $jumlahSaudaratiri;

    /**
     * @var string
     *
     * @ORM\Column(name="status_orphan", type="string", length=100, nullable=true)
     */
    private $statusOrphan;

    /**
     * @var string
     *
     * @ORM\Column(name="bahasa_seharihari", type="string", length=200, nullable=true)
     */
    private $bahasaSeharihari;

    /**
     * @var string
     *
     * @ORM\Column(name="alamat", type="string", length=500, nullable=true)
     */
    private $alamat;

    /**
     * @var string
     *
     * @ORM\Column(name="kodepos", type="string", length=30, nullable=true)
     */
    private $kodepos;

    /**
     * @var string
     *
     * @ORM\Column(name="telepon", type="string", length=100, nullable=true)
     */
    private $telepon;

    /**
     * @var string
     *
     * @ORM\Column(name="ponsel_siswa", type="string", length=100, nullable=true)
     */
    private $ponselSiswa;

    /**
     * @var string
     *
     * @ORM\Column(name="ponsel_orangtuawali", type="string", length=100, nullable=true)
     */
    private $ponselOrangtuawali;

    /**
     * @var string
     *
     * @ORM\Column(name="sekolah_tinggaldi", type="string", length=400, nullable=true)
     */
    private $sekolahTinggaldi;

    /**
     * @var string
     *
     * @ORM\Column(name="jarak_tempat", type="string", length=300, nullable=true)
     */
    private $jarakTempat;

    /**
     * @var string
     *
     * @ORM\Column(name="cara_kesekolah", type="string", length=300, nullable=true)
     */
    private $caraKesekolah;

    /**
     * @var integer
     *
     * @ORM\Column(name="beratbadan", type="integer", nullable=true)
     */
    private $beratbadan;

    /**
     * @var integer
     *
     * @ORM\Column(name="tinggibadan", type="integer", nullable=true)
     */
    private $tinggibadan;

    /**
     * @var string
     *
     * @ORM\Column(name="golongandarah", type="string", length=50, nullable=true)
     */
    private $golongandarah;

    /**
     * @var \Gelombang
     *
     * @ORM\ManyToOne(targetEntity="Gelombang")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="gelombang_id", referencedColumnName="id")
     * })
     */
    private $gelombang;

    /**
     * @var \Tahunmasuk
     *
     * @ORM\ManyToOne(targetEntity="Tahunmasuk")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tahunmasuk_id", referencedColumnName="id")
     * })
     */
    private $tahunmasuk;

    /**
     * @var \Sekolah
     *
     * @ORM\ManyToOne(targetEntity="Sekolah")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sekolah_id", referencedColumnName="id")
     * })
     */
    private $sekolah;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nomorUrutPersekolah
     *
     * @param integer $nomorUrutPersekolah
     * @return Siswa
     */
    public function setNomorUrutPersekolah($nomorUrutPersekolah) {
        $this->nomorUrutPersekolah = $nomorUrutPersekolah;

        return $this;
    }

    /**
     * Get nomorUrutPersekolah
     *
     * @return integer 
     */
    public function getNomorUrutPersekolah() {
        return $this->nomorUrutPersekolah;
    }

    /**
     * Set nomorIndukSistem
     *
     * @param string $nomorIndukSistem
     * @return Siswa
     */
    public function setNomorIndukSistem($nomorIndukSistem) {
        $this->nomorIndukSistem = $nomorIndukSistem;

        return $this;
    }

    /**
     * Get nomorIndukSistem
     *
     * @return string 
     */
    public function getNomorIndukSistem() {
        return $this->nomorIndukSistem;
    }

    /**
     * Set nomorPendaftaran
     *
     * @param integer $nomorPendaftaran
     * @return Siswa
     */
    public function setNomorPendaftaran($nomorPendaftaran) {
        $this->nomorPendaftaran = $nomorPendaftaran;

        return $this;
    }

    /**
     * Get nomorPendaftaran
     *
     * @return integer 
     */
    public function getNomorPendaftaran() {
        return $this->nomorPendaftaran;
    }

    /**
     * Set nomorInduk
     *
     * @param string $nomorInduk
     * @return Siswa
     */
    public function setNomorInduk($nomorInduk) {
        $this->nomorInduk = $nomorInduk;

        return $this;
    }

    /**
     * Get nomorInduk
     *
     * @return string 
     */
    public function getNomorInduk() {
        return $this->nomorInduk;
    }

    /**
     * Set namaLengkap
     *
     * @param string $namaLengkap
     * @return Siswa
     */
    public function setNamaLengkap($namaLengkap) {
        $this->namaLengkap = $namaLengkap;

        return $this;
    }

    /**
     * Get namaLengkap
     *
     * @return string 
     */
    public function getNamaLengkap() {
        return $this->namaLengkap;
    }

    /**
     * Set jenisKelamin
     *
     * @param string $jenisKelamin
     * @return Siswa
     */
    public function setJenisKelamin($jenisKelamin) {
        $this->jenisKelamin = $jenisKelamin;

        return $this;
    }

    /**
     * Get jenisKelamin
     *
     * @return string 
     */
    public function getJenisKelamin() {
        return $this->jenisKelamin;
    }

    /**
     * Set foto
     *
     * @param string $foto
     * @return Siswa
     */
    public function setFoto($foto) {
        $this->foto = $foto;

        return $this;
    }

    /**
     * Get foto
     *
     * @return string 
     */
    public function getFoto() {
        return $this->foto;
    }

    /**
     * Set agama
     *
     * @param string $agama
     * @return Siswa
     */
    public function setAgama($agama) {
        $this->agama = $agama;

        return $this;
    }

    /**
     * Get agama
     *
     * @return string 
     */
    public function getAgama() {
        return $this->agama;
    }

    /**
     * Set tempatLahir
     *
     * @param string $tempatLahir
     * @return Siswa
     */
    public function setTempatLahir($tempatLahir) {
        $this->tempatLahir = $tempatLahir;

        return $this;
    }

    /**
     * Get tempatLahir
     *
     * @return string 
     */
    public function getTempatLahir() {
        return $this->tempatLahir;
    }

    /**
     * Set tanggalLahir
     *
     * @param \DateTime $tanggalLahir
     * @return Siswa
     */
    public function setTanggalLahir($tanggalLahir) {
        $this->tanggalLahir = $tanggalLahir;

        return $this;
    }

    /**
     * Get tanggalLahir
     *
     * @return \DateTime 
     */
    public function getTanggalLahir() {
        return $this->tanggalLahir;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Siswa
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set namaPanggilan
     *
     * @param string $namaPanggilan
     * @return Siswa
     */
    public function setNamaPanggilan($namaPanggilan) {
        $this->namaPanggilan = $namaPanggilan;

        return $this;
    }

    /**
     * Get namaPanggilan
     *
     * @return string 
     */
    public function getNamaPanggilan() {
        return $this->namaPanggilan;
    }

    /**
     * Set kewarganegaraan
     *
     * @param string $kewarganegaraan
     * @return Siswa
     */
    public function setKewarganegaraan($kewarganegaraan) {
        $this->kewarganegaraan = $kewarganegaraan;

        return $this;
    }

    /**
     * Get kewarganegaraan
     *
     * @return string 
     */
    public function getKewarganegaraan() {
        return $this->kewarganegaraan;
    }

    /**
     * Set anakKe
     *
     * @param integer $anakKe
     * @return Siswa
     */
    public function setAnakKe($anakKe) {
        $this->anakKe = $anakKe;

        return $this;
    }

    /**
     * Get anakKe
     *
     * @return integer 
     */
    public function getAnakKe() {
        return $this->anakKe;
    }

    /**
     * Set jumlahSaudarakandung
     *
     * @param integer $jumlahSaudarakandung
     * @return Siswa
     */
    public function setJumlahSaudarakandung($jumlahSaudarakandung) {
        $this->jumlahSaudarakandung = $jumlahSaudarakandung;

        return $this;
    }

    /**
     * Get jumlahSaudarakandung
     *
     * @return integer 
     */
    public function getJumlahSaudarakandung() {
        return $this->jumlahSaudarakandung;
    }

    /**
     * Set jumlahSaudaratiri
     *
     * @param integer $jumlahSaudaratiri
     * @return Siswa
     */
    public function setJumlahSaudaratiri($jumlahSaudaratiri) {
        $this->jumlahSaudaratiri = $jumlahSaudaratiri;

        return $this;
    }

    /**
     * Get jumlahSaudaratiri
     *
     * @return integer 
     */
    public function getJumlahSaudaratiri() {
        return $this->jumlahSaudaratiri;
    }

    /**
     * Set statusOrphan
     *
     * @param string $statusOrphan
     * @return Siswa
     */
    public function setStatusOrphan($statusOrphan) {
        $this->statusOrphan = $statusOrphan;

        return $this;
    }

    /**
     * Get statusOrphan
     *
     * @return string 
     */
    public function getStatusOrphan() {
        return $this->statusOrphan;
    }

    /**
     * Set bahasaSeharihari
     *
     * @param string $bahasaSeharihari
     * @return Siswa
     */
    public function setBahasaSeharihari($bahasaSeharihari) {
        $this->bahasaSeharihari = $bahasaSeharihari;

        return $this;
    }

    /**
     * Get bahasaSeharihari
     *
     * @return string 
     */
    public function getBahasaSeharihari() {
        return $this->bahasaSeharihari;
    }

    /**
     * Set alamat
     *
     * @param string $alamat
     * @return Siswa
     */
    public function setAlamat($alamat) {
        $this->alamat = $alamat;

        return $this;
    }

    /**
     * Get alamat
     *
     * @return string 
     */
    public function getAlamat() {
        return $this->alamat;
    }

    /**
     * Set kodepos
     *
     * @param string $kodepos
     * @return Siswa
     */
    public function setKodepos($kodepos) {
        $this->kodepos = $kodepos;

        return $this;
    }

    /**
     * Get kodepos
     *
     * @return string 
     */
    public function getKodepos() {
        return $this->kodepos;
    }

    /**
     * Set telepon
     *
     * @param string $telepon
     * @return Siswa
     */
    public function setTelepon($telepon) {
        $this->telepon = $telepon;

        return $this;
    }

    /**
     * Get telepon
     *
     * @return string 
     */
    public function getTelepon() {
        return $this->telepon;
    }

    /**
     * Set ponselSiswa
     *
     * @param string $ponselSiswa
     * @return Siswa
     */
    public function setPonselSiswa($ponselSiswa) {
        $this->ponselSiswa = $ponselSiswa;

        return $this;
    }

    /**
     * Get ponselSiswa
     *
     * @return string 
     */
    public function getPonselSiswa() {
        return $this->ponselSiswa;
    }

    /**
     * Set ponselOrangtuawali
     *
     * @param string $ponselOrangtuawali
     * @return Siswa
     */
    public function setPonselOrangtuawali($ponselOrangtuawali) {
        $this->ponselOrangtuawali = $ponselOrangtuawali;

        return $this;
    }

    /**
     * Get ponselOrangtuawali
     *
     * @return string 
     */
    public function getPonselOrangtuawali() {
        return $this->ponselOrangtuawali;
    }

    /**
     * Set sekolahTinggaldi
     *
     * @param string $sekolahTinggaldi
     * @return Siswa
     */
    public function setSekolahTinggaldi($sekolahTinggaldi) {
        $this->sekolahTinggaldi = $sekolahTinggaldi;

        return $this;
    }

    /**
     * Get sekolahTinggaldi
     *
     * @return string 
     */
    public function getSekolahTinggaldi() {
        return $this->sekolahTinggaldi;
    }

    /**
     * Set jarakTempat
     *
     * @param string $jarakTempat
     * @return Siswa
     */
    public function setJarakTempat($jarakTempat) {
        $this->jarakTempat = $jarakTempat;

        return $this;
    }

    /**
     * Get jarakTempat
     *
     * @return string 
     */
    public function getJarakTempat() {
        return $this->jarakTempat;
    }

    /**
     * Set caraKesekolah
     *
     * @param string $caraKesekolah
     * @return Siswa
     */
    public function setCaraKesekolah($caraKesekolah) {
        $this->caraKesekolah = $caraKesekolah;

        return $this;
    }

    /**
     * Get caraKesekolah
     *
     * @return string 
     */
    public function getCaraKesekolah() {
        return $this->caraKesekolah;
    }

    /**
     * Set beratbadan
     *
     * @param integer $beratbadan
     * @return Siswa
     */
    public function setBeratbadan($beratbadan) {
        $this->beratbadan = $beratbadan;

        return $this;
    }

    /**
     * Get beratbadan
     *
     * @return integer 
     */
    public function getBeratbadan() {
        return $this->beratbadan;
    }

    /**
     * Set tinggibadan
     *
     * @param integer $tinggibadan
     * @return Siswa
     */
    public function setTinggibadan($tinggibadan) {
        $this->tinggibadan = $tinggibadan;

        return $this;
    }

    /**
     * Get tinggibadan
     *
     * @return integer 
     */
    public function getTinggibadan() {
        return $this->tinggibadan;
    }

    /**
     * Set golongandarah
     *
     * @param string $golongandarah
     * @return Siswa
     */
    public function setGolongandarah($golongandarah) {
        $this->golongandarah = $golongandarah;

        return $this;
    }

    /**
     * Get golongandarah
     *
     * @return string 
     */
    public function getGolongandarah() {
        return $this->golongandarah;
    }

    /**
     * Set gelombang
     *
     * @param \Fast\SisdikBundle\Entity\Gelombang $gelombang
     * @return Siswa
     */
    public function setGelombang(\Fast\SisdikBundle\Entity\Gelombang $gelombang = null) {
        $this->gelombang = $gelombang;

        return $this;
    }

    /**
     * Get gelombang
     *
     * @return \Fast\SisdikBundle\Entity\Gelombang 
     */
    public function getGelombang() {
        return $this->gelombang;
    }

    /**
     * Set tahunmasuk
     *
     * @param \Fast\SisdikBundle\Entity\Tahunmasuk $tahunmasuk
     * @return Siswa
     */
    public function setTahunmasuk(\Fast\SisdikBundle\Entity\Tahunmasuk $tahunmasuk = null) {
        $this->tahunmasuk = $tahunmasuk;

        return $this;
    }

    /**
     * Get tahunmasuk
     *
     * @return \Fast\SisdikBundle\Entity\Tahunmasuk 
     */
    public function getTahunmasuk() {
        return $this->tahunmasuk;
    }

    /**
     * Set sekolah
     *
     * @param \Fast\SisdikBundle\Entity\Sekolah $sekolah
     * @return Siswa
     */
    public function setSekolah(\Fast\SisdikBundle\Entity\Sekolah $sekolah = null) {
        $this->sekolah = $sekolah;

        return $this;
    }

    /**
     * Get sekolah
     *
     * @return \Fast\SisdikBundle\Entity\Sekolah 
     */
    public function getSekolah() {
        return $this->sekolah;
    }

    /**
     * @Assert\File(maxSize="5000000")
     */
    private $file;

    const PHOTO_DIR = 'uploads/students/photos';
    const THUMBNAIL_PREFIX = 'th1-';
    const MEMORY_LIMIT = '256M';
    const PHOTO_THUMB_WIDTH = 80;
    const PHOTO_THUMB_HEIGHT = 150;

    public function getFile() {
        return $this->file;
    }

    public function setFile(UploadedFile $file) {
        $this->file = $file;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload() {
        if (null !== $this->file) {
            $this->foto = sha1(uniqid(mt_rand(), true)) . '.' . $this->file->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload() {
        if (null === $this->file) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        if ($this->file->move($this->getUploadRootDir(), $this->foto)) {

            $targetfile = $this->getAbsolutePath();
            $thumbnailfile = $this->getUploadRootDir() . '/' . self::THUMBNAIL_PREFIX . $this->foto;

            list($origWidth, $origHeight, $type, $attr) = @getimagesize($targetfile);
            if (is_numeric($type)) {

                $origRatio = $origWidth / $origHeight;
                $resultWidth = self::PHOTO_THUMB_WIDTH;
                $resultHeight = self::PHOTO_THUMB_HEIGHT;
                if ($resultWidth / $resultHeight > $origRatio) {
                    $resultWidth = $resultHeight * $origRatio;
                } else {
                    $resultHeight = $resultWidth / $origRatio;
                }

                // Set artificially high because GD uses uncompressed images in memory
                @ini_set('memory_limit', self::MEMORY_LIMIT);

                switch ($type) {
                    case IMAGETYPE_JPEG:
                        if (imagetypes() & IMG_JPEG) {
                            // resample image
                            $resultImage = imagecreatetruecolor($resultWidth, $resultHeight);

                            $srcImage = imagecreatefromjpeg($targetfile);
                            imagecopyresampled($resultImage, $srcImage, 0, 0, 0, 0, $resultWidth,
                                    $resultHeight, $origWidth, $origHeight);

                            imagejpeg($resultImage, $thumbnailfile, 90);
                        }
                        break;
                    case IMAGETYPE_PNG:
                        if (imagetypes() & IMG_PNG) {
                            // resample image
                            // for png, we use imagecreate instead
                            $resultImage = imagecreate($resultWidth, $resultHeight);

                            $srcImage = imagecreatefrompng($targetfile);
                            imagecopyresampled($resultImage, $srcImage, 0, 0, 0, 0, $resultWidth,
                                    $resultHeight, $origWidth, $origHeight);

                            imagepng($resultImage, $thumbnailfile, 8);
                        }
                        break;
                    case IMAGETYPE_GIF:
                        if (imagetypes() & IMG_GIF) {
                            // resample image
                            $resultImage = imagecreatetruecolor($resultWidth, $resultHeight);

                            $srcImage = imagecreatefromgif($targetfile);
                            imagecopyresampled($resultImage, $srcImage, 0, 0, 0, 0, $resultWidth,
                                    $resultHeight, $origWidth, $origHeight);

                            imagegif($resultImage, $thumbnailfile);
                        }
                        break;
                }
            }
        }

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload() {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    public function getAbsolutePath() {
        return null === $this->foto ? null : $this->getUploadRootDir() . '/' . $this->foto;
    }

    public function getWebPath() {
        return null === $this->foto ? null : $this->getUploadDir() . '/' . $this->foto;
    }

    public function getWebPathThumbnail() {
        return null === $this->foto ? null
                : $this->getUploadDir() . '/' . self::THUMBNAIL_PREFIX . $this->foto;
    }

    protected function getUploadRootDir() {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir() {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return self::PHOTO_DIR;
    }
}
