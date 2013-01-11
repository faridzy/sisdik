<?php

namespace Fast\SisdikBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tahunmasuk
 *
 * @ORM\Table(name="tahunmasuk")
 * @ORM\Entity
 */
class Tahunmasuk
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
     * @var \DateTime
     *
     * @ORM\Column(name="tahun", type="string", nullable=false)
     */
    private $tahun;

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
     * Set tahun
     *
     * @param \DateTime $tahun
     * @return Tahunmasuk
     */
    public function setTahun($tahun) {
        $this->tahun = $tahun;

        return $this;
    }

    /**
     * Get tahun
     *
     * @return \DateTime 
     */
    public function getTahun() {
        return $this->tahun;
    }

    /**
     * Set sekolah
     *
     * @param \Fast\SisdikBundle\Entity\Sekolah $sekolah
     * @return Tahunmasuk
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
}
