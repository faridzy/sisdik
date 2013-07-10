<?php

namespace Fast\SisdikBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Kelas
 *
 * @ORM\Table(name="kelas")
 * @ORM\Entity
 */
class Kelas
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
     * @var string
     *
     * @ORM\Column(name="nama", type="string", length=300, nullable=true)
     */
    private $nama;

    /**
     * @var string
     *
     * @ORM\Column(name="kode", type="string", length=50, nullable=true)
     */
    private $kode;

    /**
     * @var string
     *
     * @ORM\Column(name="keterangan", type="string", length=400, nullable=true)
     */
    private $keterangan;

    /**
     * @var integer
     *
     * @ORM\Column(name="urutan", type="smallint", nullable=true)
     */
    private $urutan;

    /**
     * @var \TahunAkademik
     *
     * @ORM\ManyToOne(targetEntity="TahunAkademik")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tahun_akademik_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $tahunAkademik;

    /**
     * @var \Sekolah
     *
     * @ORM\ManyToOne(targetEntity="Sekolah")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sekolah_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $sekolah;

    /**
     * @var \Tingkat
     *
     * @ORM\ManyToOne(targetEntity="Tingkat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tingkat_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $tingkat;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nama
     *
     * @param string $nama
     * @return Kelas
     */
    public function setNama($nama) {
        $this->nama = $nama;

        return $this;
    }

    /**
     * Get nama
     *
     * @return string
     */
    public function getNama() {
        return $this->nama;
    }

    /**
     * Set kode
     *
     * @param string $kode
     * @return Kelas
     */
    public function setKode($kode) {
        $this->kode = $kode;

        return $this;
    }

    /**
     * Get kode
     *
     * @return string
     */
    public function getKode() {
        return $this->kode;
    }

    /**
     * Set keterangan
     *
     * @param string $keterangan
     * @return Kelas
     */
    public function setKeterangan($keterangan) {
        $this->keterangan = $keterangan;

        return $this;
    }

    /**
     * Get keterangan
     *
     * @return string
     */
    public function getKeterangan() {
        return $this->keterangan;
    }

    /**
     * Set urutan
     *
     * @param integer $urutan
     * @return Kelas
     */
    public function setUrutan($urutan) {
        $this->urutan = $urutan;

        return $this;
    }

    /**
     * Get urutan
     *
     * @return integer
     */
    public function getUrutan() {
        return $this->urutan;
    }

    /**
     * Set tahunAkademik
     *
     * @param \Fast\SisdikBundle\Entity\TahunAkademik $tahunAkademik
     * @return Kelas
     */
    public function setTahunAkademik(\Fast\SisdikBundle\Entity\TahunAkademik $tahunAkademik = null) {
        $this->tahunAkademik = $tahunAkademik;

        return $this;
    }

    /**
     * Get tahunAkademik
     *
     * @return \Fast\SisdikBundle\Entity\TahunAkademik
     */
    public function getTahunAkademik() {
        return $this->tahunAkademik;
    }

    /**
     * Set sekolah
     *
     * @param \Fast\SisdikBundle\Entity\Sekolah $sekolah
     * @return Kelas
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
     * Set tingkat
     *
     * @param \Fast\SisdikBundle\Entity\Tingkat $tingkat
     * @return Kelas
     */
    public function setTingkat(\Fast\SisdikBundle\Entity\Tingkat $tingkat = null) {
        $this->tingkat = $tingkat;

        return $this;
    }

    /**
     * Get tingkat
     *
     * @return \Fast\SisdikBundle\Entity\Tingkat
     */
    public function getTingkat() {
        return $this->tingkat;
    }
}
