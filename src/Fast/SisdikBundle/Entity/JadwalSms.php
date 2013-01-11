<?php

namespace Fast\SisdikBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Fast\SisdikBundle\Entity\JadwalSms
 *
 * @ORM\Table(name="jadwal_sms")
 * @ORM\Entity
 */
class JadwalSms
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $perulangan
     *
     * @ORM\Column(name="perulangan", type="string", nullable=true)
     */
    private $perulangan;

    /**
     * @var boolean $mingguanHariKe
     *
     * @ORM\Column(name="mingguan_hari_ke", type="boolean", nullable=true)
     */
    private $mingguanHariKe;

    /**
     * @var boolean $bulananHariKe
     *
     * @ORM\Column(name="bulanan_hari_ke", type="boolean", nullable=true)
     */
    private $bulananHariKe;

    /**
     * @var string $dariJam
     *
     * @ORM\Column(name="dari_jam", type="string", length=50, nullable=true)
     */
    private $dariJam;

    /**
     * @var string $hinggaJam
     *
     * @ORM\Column(name="hingga_jam", type="string", length=50, nullable=true)
     */
    private $hinggaJam;

    /**
     * @var boolean $kategori
     *
     * @ORM\Column(name="kategori", type="boolean", nullable=true)
     */
    private $kategori;

    /**
     * @var integer $urutanPeriksa
     *
     * @ORM\Column(name="urutan_periksa", type="smallint", nullable=true)
     */
    private $urutanPeriksa;

    /**
     * @var Tahun
     *
     * @ORM\ManyToOne(targetEntity="Tahun")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tahun", referencedColumnName="id")
     * })
     */
    private $tahun;

    /**
     * @var Kelas
     *
     * @ORM\ManyToOne(targetEntity="Kelas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="kelas", referencedColumnName="id")
     * })
     */
    private $kelas;

    /**
     * @var Templatesms
     *
     * @ORM\ManyToOne(targetEntity="Templatesms")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="templatesms", referencedColumnName="id")
     * })
     */
    private $templatesms;



    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set perulangan
     *
     * @param string $perulangan
     * @return JadwalSms
     */
    public function setPerulangan($perulangan)
    {
        $this->perulangan = $perulangan;
    
        return $this;
    }

    /**
     * Get perulangan
     *
     * @return string 
     */
    public function getPerulangan()
    {
        return $this->perulangan;
    }

    /**
     * Set mingguanHariKe
     *
     * @param boolean $mingguanHariKe
     * @return JadwalSms
     */
    public function setMingguanHariKe($mingguanHariKe)
    {
        $this->mingguanHariKe = $mingguanHariKe;
    
        return $this;
    }

    /**
     * Get mingguanHariKe
     *
     * @return boolean 
     */
    public function getMingguanHariKe()
    {
        return $this->mingguanHariKe;
    }

    /**
     * Set bulananHariKe
     *
     * @param boolean $bulananHariKe
     * @return JadwalSms
     */
    public function setBulananHariKe($bulananHariKe)
    {
        $this->bulananHariKe = $bulananHariKe;
    
        return $this;
    }

    /**
     * Get bulananHariKe
     *
     * @return boolean 
     */
    public function getBulananHariKe()
    {
        return $this->bulananHariKe;
    }

    /**
     * Set dariJam
     *
     * @param string $dariJam
     * @return JadwalSms
     */
    public function setDariJam($dariJam)
    {
        $this->dariJam = $dariJam;
    
        return $this;
    }

    /**
     * Get dariJam
     *
     * @return string 
     */
    public function getDariJam()
    {
        return $this->dariJam;
    }

    /**
     * Set hinggaJam
     *
     * @param string $hinggaJam
     * @return JadwalSms
     */
    public function setHinggaJam($hinggaJam)
    {
        $this->hinggaJam = $hinggaJam;
    
        return $this;
    }

    /**
     * Get hinggaJam
     *
     * @return string 
     */
    public function getHinggaJam()
    {
        return $this->hinggaJam;
    }

    /**
     * Set kategori
     *
     * @param boolean $kategori
     * @return JadwalSms
     */
    public function setKategori($kategori)
    {
        $this->kategori = $kategori;
    
        return $this;
    }

    /**
     * Get kategori
     *
     * @return boolean 
     */
    public function getKategori()
    {
        return $this->kategori;
    }

    /**
     * Set urutanPeriksa
     *
     * @param integer $urutanPeriksa
     * @return JadwalSms
     */
    public function setUrutanPeriksa($urutanPeriksa)
    {
        $this->urutanPeriksa = $urutanPeriksa;
    
        return $this;
    }

    /**
     * Get urutanPeriksa
     *
     * @return integer 
     */
    public function getUrutanPeriksa()
    {
        return $this->urutanPeriksa;
    }

    /**
     * Set tahun
     *
     * @param Fast\SisdikBundle\Entity\Tahun $tahun
     * @return JadwalSms
     */
    public function setIdtahun(\Fast\SisdikBundle\Entity\Tahun $tahun = null)
    {
        $this->tahun = $tahun;
    
        return $this;
    }

    /**
     * Get tahun
     *
     * @return Fast\SisdikBundle\Entity\Tahun 
     */
    public function getIdtahun()
    {
        return $this->tahun;
    }

    /**
     * Set kelas
     *
     * @param Fast\SisdikBundle\Entity\Kelas $kelas
     * @return JadwalSms
     */
    public function setIdkelas(\Fast\SisdikBundle\Entity\Kelas $kelas = null)
    {
        $this->kelas = $kelas;
    
        return $this;
    }

    /**
     * Get kelas
     *
     * @return Fast\SisdikBundle\Entity\Kelas 
     */
    public function getIdkelas()
    {
        return $this->kelas;
    }

    /**
     * Set templatesms
     *
     * @param Fast\SisdikBundle\Entity\Templatesms $templatesms
     * @return JadwalSms
     */
    public function setIdtemplatesms(\Fast\SisdikBundle\Entity\Templatesms $templatesms = null)
    {
        $this->templatesms = $templatesms;
    
        return $this;
    }

    /**
     * Get templatesms
     *
     * @return Fast\SisdikBundle\Entity\Templatesms 
     */
    public function getIdtemplatesms()
    {
        return $this->templatesms;
    }

    /**
     * Set tahun
     *
     * @param \Fast\SisdikBundle\Entity\Tahun $tahun
     * @return JadwalSms
     */
    public function setTahun(\Fast\SisdikBundle\Entity\Tahun $tahun = null)
    {
        $this->tahun = $tahun;
    
        return $this;
    }

    /**
     * Get tahun
     *
     * @return \Fast\SisdikBundle\Entity\Tahun 
     */
    public function getTahun()
    {
        return $this->tahun;
    }

    /**
     * Set kelas
     *
     * @param \Fast\SisdikBundle\Entity\Kelas $kelas
     * @return JadwalSms
     */
    public function setKelas(\Fast\SisdikBundle\Entity\Kelas $kelas = null)
    {
        $this->kelas = $kelas;
    
        return $this;
    }

    /**
     * Get kelas
     *
     * @return \Fast\SisdikBundle\Entity\Kelas 
     */
    public function getKelas()
    {
        return $this->kelas;
    }

    /**
     * Set templatesms
     *
     * @param \Fast\SisdikBundle\Entity\Templatesms $templatesms
     * @return JadwalSms
     */
    public function setTemplatesms(\Fast\SisdikBundle\Entity\Templatesms $templatesms = null)
    {
        $this->templatesms = $templatesms;
    
        return $this;
    }

    /**
     * Get templatesms
     *
     * @return \Fast\SisdikBundle\Entity\Templatesms 
     */
    public function getTemplatesms()
    {
        return $this->templatesms;
    }
}