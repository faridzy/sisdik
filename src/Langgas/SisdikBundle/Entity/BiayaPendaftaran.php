<?php

namespace Langgas\SisdikBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="biaya_pendaftaran")
 * @ORM\Entity
 */
class BiayaPendaftaran
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(name="nominal", type="bigint", nullable=false, options={"default" = 0})
     * @Assert\GreaterThanOrEqual(value=0)
     *
     * @var integer
     */
    private $nominal;

    /**
     * @var integer
     */
    private $nominalSebelumnya;

    /**
     * @ORM\Column(name="urutan", type="smallint", nullable=true)
     *
     * @var integer
     */
    private $urutan;

    /**
     * @ORM\Column(name="terpakai", type="boolean", nullable=false, options={"default" = 0})
     *
     * @var boolean
     */
    private $terpakai = false;

    /**
     * @ORM\ManyToOne(targetEntity="Jenisbiaya")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="jenisbiaya_id", referencedColumnName="id", nullable=false)
     * })
     * @Assert\NotNull
     *
     * @var Jenisbiaya
     */
    private $jenisbiaya;

    /**
     * @ORM\ManyToOne(targetEntity="Tahun")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="tahun_id", referencedColumnName="id", nullable=false)
     * })
     * @Assert\NotNull
     *
     * @var Tahun
     */
    private $tahun;

    /**
     * @ORM\ManyToOne(targetEntity="Gelombang")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="gelombang_id", referencedColumnName="id", nullable=false)
     * })
     * @Assert\NotNull
     *
     * @var Gelombang
     */
    private $gelombang;

    /**
     * @ORM\ManyToOne(targetEntity="Penjurusan")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="penjurusan_id", referencedColumnName="id", nullable=true)
     * })
     *
     * @var Penjurusan
     */
    private $penjurusan;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $nominal
     */
    public function setNominal($nominal)
    {
        $this->nominal = $nominal;
    }

    /**
     * @return integer
     */
    public function getNominal()
    {
        return $this->nominal;
    }

    /**
     * @param integer $nominalSebelumnya
     */
    public function setNominalSebelumnya($nominalSebelumnya)
    {
        $this->nominalSebelumnya = $nominalSebelumnya;
    }

    /**
     * @return integer
     */
    public function getNominalSebelumnya()
    {
        return $this->nominalSebelumnya;
    }

    /**
     * @param integer $urutan
     */
    public function setUrutan($urutan)
    {
        $this->urutan = $urutan;
    }

    /**
     * @return integer
     */
    public function getUrutan()
    {
        return $this->urutan;
    }

    /**
     * @param boolean $terpakai
     */
    public function setTerpakai($terpakai)
    {
        $this->terpakai = $terpakai;
    }

    /**
     * @return boolean
     */
    public function isTerpakai()
    {
        return $this->terpakai;
    }

    /**
     * @param Jenisbiaya $jenisbiaya
     */
    public function setJenisbiaya(Jenisbiaya $jenisbiaya = null)
    {
        $this->jenisbiaya = $jenisbiaya;
    }

    /**
     * @return Jenisbiaya
     */
    public function getJenisbiaya()
    {
        return $this->jenisbiaya;
    }

    /**
     * @param Tahun $tahun
     */
    public function setTahun(Tahun $tahun = null)
    {
        $this->tahun = $tahun;
    }

    /**
     * @return Tahun
     */
    public function getTahun()
    {
        return $this->tahun;
    }

    /**
     * @param Gelombang $gelombang
     */
    public function setGelombang(Gelombang $gelombang = null)
    {
        $this->gelombang = $gelombang;
    }

    /**
     * @return Gelombang
     */
    public function getGelombang()
    {
        return $this->gelombang;
    }

    /**
     * @param Penjurusan $penjurusan
     */
    public function setPenjurusan(Penjurusan $penjurusan = null)
    {
        $this->penjurusan = $penjurusan;
    }

    /**
     * @return Penjurusan
     */
    public function getPenjurusan()
    {
        return $this->penjurusan;
    }
}
