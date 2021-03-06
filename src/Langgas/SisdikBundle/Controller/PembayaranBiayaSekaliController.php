<?php

namespace Langgas\SisdikBundle\Controller;

use Doctrine\ORM\EntityManager;
use Langgas\SisdikBundle\Entity\BiayaSekali;
use Langgas\SisdikBundle\Entity\DaftarBiayaSekali;
use Langgas\SisdikBundle\Entity\LayananSms;
use Langgas\SisdikBundle\Entity\OrangtuaWali;
use Langgas\SisdikBundle\Entity\PembayaranSekali;
use Langgas\SisdikBundle\Entity\Penjurusan;
use Langgas\SisdikBundle\Entity\PilihanLayananSms;
use Langgas\SisdikBundle\Entity\Sekolah;
use Langgas\SisdikBundle\Entity\Siswa;
use Langgas\SisdikBundle\Entity\Tahun;
use Langgas\SisdikBundle\Entity\TransaksiPembayaranSekali;
use Langgas\SisdikBundle\Entity\VendorSekolah;
use Langgas\SisdikBundle\Util\Messenger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

/**
 * @Route("/pembayaran-biaya-sekali-bayar")
 * @PreAuthorize("hasAnyRole('ROLE_BENDAHARA', 'ROLE_KASIR')")
 */
class PembayaranBiayaSekaliController extends Controller
{
    /**
     * @Route("/", name="pembayaran_biaya_sekali__daftar")
     * @Template()
     */
    public function indexAction()
    {
        $sekolah = $this->getSekolah();
        $this->setCurrentMenu();

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $searchkey = '';
        $tampilkanTercari = false;

        $searchform = $this->createForm('sisdik_caripembayarbiayasekali');

        $pendaftarTotal = $em->createQueryBuilder()
            ->select('COUNT(siswa.id)')
            ->from('LanggasSisdikBundle:Siswa', 'siswa')
            ->where('siswa.sekolah = :sekolah')
            ->andWhere('siswa.calonSiswa = :calon')
            ->setParameter('sekolah', $sekolah)
            ->setParameter('calon', false)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $querybuilder = $em->createQueryBuilder()
            ->select('siswa')
            ->from('LanggasSisdikBundle:Siswa', 'siswa')
            ->leftJoin('siswa.tahun', 'tahun')
            ->where('siswa.sekolah = :sekolah')
            ->andWhere('siswa.calonSiswa = :calon')
            ->orderBy('tahun.tahun', 'DESC')
            ->addOrderBy('siswa.nomorIndukSistem', 'DESC')
            ->setParameter('sekolah', $sekolah)
            ->setParameter('calon', 0)
        ;

        $searchform->submit($this->getRequest());
        if ($searchform->isValid()) {
            $searchdata = $searchform->getData();

            $querybuilder
                ->leftJoin('siswa.pembayaranSekali', 'pembayaran')
                ->leftJoin('pembayaran.transaksiPembayaranSekali', 'transaksi')
            ;

            if ($searchdata['tahun'] instanceof Tahun) {
                $querybuilder
                    ->andWhere('siswa.tahun = :tahun')
                    ->setParameter('tahun', $searchdata['tahun'])
                ;

                $tampilkanTercari = true;
            }

            if ($searchdata['searchkey'] != '') {
                $searchkey = $searchdata['searchkey'];

                $querybuilder
                    ->andWhere(
                        'siswa.namaLengkap LIKE :namalengkap '
                        .' OR siswa.nomorIndukSistem LIKE :identitas '
                        .' OR siswa.keterangan LIKE :keterangan '
                        .' OR transaksi.nomorTransaksi = :nomortransaksi '
                    )
                    ->setParameter('namalengkap', "%{$searchdata['searchkey']}%")
                    ->setParameter('identitas', "%{$searchdata['searchkey']}%")
                    ->setParameter('keterangan', "%{$searchdata['searchkey']}%")
                    ->setParameter('nomortransaksi', $searchdata['searchkey'])
                ;

                $tampilkanTercari = true;
            }

            if ($searchdata['nopayment'] === true) {
                $querybuilder
                    ->andWhere("transaksi.nominalPembayaran IS NULL")
                ;

                $tampilkanTercari = true;
            }

            if ($searchdata['nopayment'] === false && $searchdata['notsettled'] === true) {
                $querybuilder
                    ->groupBy('siswa.id')
                    ->having("SUM(pembayaran.nominalTotalTransaksi) < (SUM(DISTINCT(siswa.sisaBiayaSekali)) + SUM(pembayaran.nominalTotalBiaya) - (SUM(pembayaran.nominalPotongan) + SUM(pembayaran.persenPotonganDinominalkan))) OR SUM(DISTINCT(siswa.sisaBiayaSekali)) < 0")
                ;

                $tampilkanTercari = true;
            }
        }

        $qbTercari = clone $querybuilder;
        $pendaftarTercari = count($qbTercari->select('DISTINCT(siswa.id)')->getQuery()->getResult());

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($querybuilder, $this->getRequest()->query->get('page', 1), 5, ['wrap-queries' => true]);

        return [
            'pagination' => $pagination,
            'searchform' => $searchform->createView(),
            'pendaftarTotal' => $pendaftarTotal,
            'pendaftarTercari' => $pendaftarTercari,
            'tampilkanTercari' => $tampilkanTercari,
            'searchkey' => $searchkey,
        ];
    }

    /**
     * @Route("/{sid}", name="pembayaran_biaya_sekali__summary")
     * @Method("GET")
     * @Template()
     */
    public function summaryAction($sid)
    {
        $sekolah = $this->getSekolah();
        $this->setCurrentMenu();

        $em = $this->getDoctrine()->getManager();

        $siswa = $em->getRepository('LanggasSisdikBundle:Siswa')->find($sid);
        if (!(is_object($siswa) && $siswa instanceof Siswa)) {
            throw $this->createNotFoundException('Entity Siswa tak ditemukan.');
        }

        if ($this->get('security.authorization_checker')->isGranted('view', $siswa) === false) {
            throw new AccessDeniedException($this->get('translator')->trans('akses.ditolak'));
        }

        $entities = $em->getRepository('LanggasSisdikBundle:PembayaranSekali')->findBy(['siswa' => $siswa]);

        $itemBiaya = $this->getBiayaProperties($siswa);

        if (count($itemBiaya['semua']) == count($itemBiaya['tersimpan']) && count($itemBiaya['tersimpan']) > 0 && count($itemBiaya['tersisa']) <= 0) {
            return [
                'entities' => $entities,
                'siswa' => $siswa,
                'itemBiayaSemua' => $itemBiaya['semua'],
                'itemBiayaTersimpan' => $itemBiaya['tersimpan'],
                'itemBiayaTersisa' => $itemBiaya['tersisa'],
            ];
        } else {
            $entity = new PembayaranSekali();
            $entity->setJenisPotongan("nominal");

            foreach ($itemBiaya['tersisa'] as $id) {
                $biaya = $em->getRepository('LanggasSisdikBundle:BiayaSekali')->find($id);

                $daftarBiaya = new DaftarBiayaSekali();
                $daftarBiaya->setBiayaSekali($biaya);
                $daftarBiaya->setNama($biaya->getJenisbiaya()->getNama());
                $daftarBiaya->setNominal($biaya->getNominal());

                $entity->getDaftarBiayaSekali()->add($daftarBiaya);
            }

            $transaksiPembayaranSekali = new TransaksiPembayaranSekali();
            $entity->getTransaksiPembayaranSekali()->add($transaksiPembayaranSekali);
            $entity->setSiswa($siswa);

            $form = $this->createForm('sisdik_pembayaransekali', $entity);

            return [
                'entities' => $entities,
                'siswa' => $siswa,
                'itemBiayaSemua' => $itemBiaya['semua'],
                'itemBiayaTersimpan' => $itemBiaya['tersimpan'],
                'itemBiayaTersisa' => $itemBiaya['tersisa'],
                'form' => $form->createView(),
            ];
        }
    }

    /**
     * @Route("/{sid}", name="pembayaran_biaya_sekali__create")
     * @Method("POST")
     * @Template("LanggasSisdikBundle:PembayaranSekali:summary.html.twig")
     */
    public function createAction($sid)
    {
        $sekolah = $this->getSekolah();
        $this->setCurrentMenu();

        $em = $this->getDoctrine()->getManager();

        /* @var $siswa Siswa */
        $siswa = $em->getRepository('LanggasSisdikBundle:Siswa')->find($sid);
        if (!(is_object($siswa) && $siswa instanceof Siswa)) {
            throw $this->createNotFoundException('Entity Siswa tak ditemukan.');
        }

        if ($this->get('security.authorization_checker')->isGranted('create', $siswa) === false) {
            throw new AccessDeniedException($this->get('translator')->trans('akses.ditolak'));
        }

        $entities = $em->getRepository('LanggasSisdikBundle:PembayaranSekali')->findBy(['siswa' => $siswa]);

        $itemBiaya = $this->getBiayaProperties($siswa);

        $entity = new PembayaranSekali();
        $form = $this->createForm('sisdik_pembayaransekali', $entity);
        $form->submit($this->getRequest());

        // periksa apakah item pembayaran yang akan dimasukkan telah ada di database
        // ini untuk mencegah input ganda
        $formDaftarBiayaSekali = $form->get('daftarBiayaSekali')->getData();
        foreach ($formDaftarBiayaSekali as $item) {
            if ($item instanceof DaftarBiayaSekali) {
                if (in_array($item->getBiayaSekali()->getId(), $itemBiaya['tersimpan'])) {
                    $this
                        ->get('session')
                        ->getFlashBag()
                        ->add('error', $this->get('translator')->trans('alert.biaya.sekali.bayar.telah.tersimpan'))
                    ;

                    return $this->redirect($this->generateUrl('pembayaran_biaya_sekali__summary', [
                        'sid' => $sid,
                    ]));
                }
            }
        }

        if ($form->isValid()) {
            $entity->setSiswa($siswa);

            $now = new \DateTime();
            $qbmaxnum = $em->createQueryBuilder()
                ->select('MAX(transaksi.nomorUrutTransaksiPerbulan)')
                ->from('LanggasSisdikBundle:TransaksiPembayaranSekali', 'transaksi')
                ->where("YEAR(transaksi.waktuSimpan) = :tahunsimpan")
                ->andWhere("MONTH(transaksi.waktuSimpan) = :bulansimpan")
                ->andWhere('transaksi.sekolah = :sekolah')
                ->setParameter('tahunsimpan', $now->format('Y'))
                ->setParameter('bulansimpan', $now->format('m'))
                ->setParameter('sekolah', $sekolah)
            ;
            $nomormax = intval($qbmaxnum->getQuery()->getSingleScalarResult());
            $nomormax++;

            $currentPaymentAmount = 0;
            $transaksi = $entity->getTransaksiPembayaranSekali()->first();
            if ($transaksi instanceof TransaksiPembayaranSekali) {
                $currentPaymentAmount = $transaksi->getNominalPembayaran();
                $transaksi->setNomorUrutTransaksiPerbulan($nomormax);

                if ($sekolah->getAtributNomorTransaksiBiayaSekali() !== null) {
                    $nomorTransaksiSekali = $sekolah->getAtributNomorTransaksiBiayaSekali();

                    $nomorTransaksiSekali = str_replace("%tahun%", $now->format('Y'), $nomorTransaksiSekali);
                    $nomorTransaksiSekali = str_replace("%bulan%", $now->format('m'), $nomorTransaksiSekali);
                    $nomorTransaksiSekali = str_replace("%tanggal%", $now->format('d'), $nomorTransaksiSekali);

                    $tmpNomorTransaksi = $nomormax;

                    $matches = [];
                    $penambah = preg_match('/{\+(\d+)}/', $nomorTransaksiSekali, $matches);
                    if ($penambah === 1) {
                        $tmpNomorTransaksi = $tmpNomorTransaksi + $matches[1];
                    }
                    $nomorTransaksiSekali = preg_replace('/{\+\d+}/', '', $nomorTransaksiSekali);

                    if (preg_match('/#+%nomor-urut-perbulan%/', $nomorTransaksiSekali) === 1) {
                        $placeholder = preg_match_all('/#/', $nomorTransaksiSekali);
                        if ($placeholder >= 1 && strlen($tmpNomorTransaksi) <= $placeholder) {
                            $tmpNomorTransaksi = str_repeat('0', $placeholder - strlen($tmpNomorTransaksi) + 1).$tmpNomorTransaksi;
                        }
                    }
                    $nomorTransaksiSekali = str_replace('#', '', $nomorTransaksiSekali);

                    $nomorTransaksiSekali = str_replace("%nomor-urut-perbulan%", $tmpNomorTransaksi, $nomorTransaksiSekali);

                    $transaksi->setNomorTransaksi($nomorTransaksiSekali);
                } else {
                    $transaksi->setNomorTransaksi(TransaksiPembayaranSekali::tandakwitansi.$now->format('Y').$now->format('m').$nomormax);
                }
            }

            $entity->setNominalTotalTransaksi($entity->getNominalTotalTransaksi() + $currentPaymentAmount);

            $nominalBiaya = 0;
            $itemBiayaTerproses = [];
            foreach ($entity->getDaftarBiayaSekali() as $biaya) {
                if ($biaya instanceof DaftarBiayaSekali) {
                    if (!$biaya->isTerpilih()) {
                        $entity->getDaftarBiayaSekali()->removeElement($biaya);
                        continue;
                    }
                    $nominalBiaya += $biaya->getNominal();
                    $itemBiayaTerproses[] = $biaya->getBiayaSekali()->getId();

                    $biayaSekaliTmp = $em->getRepository('LanggasSisdikBundle:BiayaSekali')->find($biaya->getBiayaSekali()->getId());
                    $biayaSekaliTmp->setTerpakai(true);

                    $em->persist($biayaSekaliTmp);
                }
            }
            $entity->setNominalTotalBiaya($nominalBiaya);

            if ($entity->getAdaPotongan() === false) {
                $entity->setJenisPotongan(null);
                $entity->setNominalPotongan(0);
                $entity->setPersenPotongan(0);
                $entity->setPersenPotonganDinominalkan(0);
            }

            if ($entity->getAdaPotongan() && $entity->getPersenPotongan() != 0) {
                $persenPotonganDinominalkan = $nominalBiaya * ($entity->getPersenPotongan() / 100);
                $entity->setPersenPotonganDinominalkan($persenPotonganDinominalkan);
                $entity->setNominalPotongan(0);
            } else {
                $entity->setPersenPotongan(0);
                $entity->setPersenPotonganDinominalkan(0);
            }
            $currentDiscount = $entity->getNominalPotongan() + $entity->getPersenPotonganDinominalkan();

            $payableAmountDue = $siswa->getTotalNominalBiayaSekali();
            $payableAmountRemain = $this->getPayableFeesRemain(
                $siswa->getTahun(),
                array_diff($itemBiaya['tersisa'], $itemBiayaTerproses),
                $siswa->getPenjurusan()
            );

            $totalPayment = $siswa->getTotalNominalPembayaranSekali() + $currentPaymentAmount;
            $totalDiscount = $siswa->getTotalPotonganPembayaranSekali() + $currentDiscount;

            $totalInfoResponse = $this->forward('LanggasSisdikBundle:BiayaSekali:getFeeInfoTotal', [
                'tahun' => $siswa->getTahun()->getId(),
                'penjurusan' => $siswa->getPenjurusan() instanceof Penjurusan ? $siswa->getPenjurusan()->getId() : -999,
                'json' => 1,
            ]);
            $totalFee = json_decode($totalInfoResponse->getContent());

            if (($payableAmountRemain + $payableAmountDue) == ($totalPayment + $totalDiscount) || $totalFee->biaya == ($totalPayment + $totalDiscount)) {
                $siswa->setLunasBiayaSekali(true);
            }
            $siswa->setSisaBiayaSekali($payableAmountRemain);

            // print("\$totalFee: {$totalFee->biaya}<br />");
            // print("\$totalPayment: $totalPayment<br />");
            // print("\$totalDiscount: $totalDiscount<br />");
            // print("\$payableAmountDue: $payableAmountDue<br />");
            // print("\$payableAmountRemain: $payableAmountRemain<br />");
            // exit;

            $em->persist($entity);
            $em->persist($siswa);

            $em->flush();

            $vendorSekolah = $em->getRepository('LanggasSisdikBundle:VendorSekolah')
                ->findOneBy([
                    'sekolah' => $sekolah,
                ])
            ;

            $pilihanLayananSms = $em->getRepository('LanggasSisdikBundle:PilihanLayananSms')
                ->findOneBy([
                    'sekolah' => $sekolah,
                    'jenisLayanan' => 'za-biaya-sekali-bayar',
                    'status' => true,
                ])
            ;

            if ($pilihanLayananSms instanceof PilihanLayananSms) {
                if ($pilihanLayananSms->getStatus()) {
                    $layanan = $em->getRepository('LanggasSisdikBundle:LayananSms')
                        ->findOneBy([
                            'sekolah' => $sekolah,
                            'jenisLayanan' => 'za-biaya-sekali-bayar',
                        ])
                    ;
                    if ($layanan instanceof LayananSms) {
                        $tekstemplate = $layanan->getTemplatesms()->getTeks();

                        $namaOrtuWali = "";
                        $ponselOrtuWali = "";
                        $orangtuaWaliAktif = $siswa->getOrangtuaWaliAktif();
                        if ($orangtuaWaliAktif instanceof OrangtuaWali) {
                            $namaOrtuWali = $orangtuaWaliAktif->getNama();
                            $ponselOrtuWali = $orangtuaWaliAktif->getPonsel();
                        }

                        $tekstemplate = str_replace("%nama-ortuwali%", $namaOrtuWali, $tekstemplate);
                        $tekstemplate = str_replace("%nama-siswa%", $siswa->getNamaLengkap(), $tekstemplate);

                        $nomorTransaksi = "";
                        $em->refresh($entity);
                        foreach ($entity->getTransaksiPembayaranSekali() as $transaksi) {
                            if ($transaksi instanceof TransaksiPembayaranSekali) {
                                $em->refresh($transaksi);
                                $nomorTransaksi = $transaksi->getNomorTransaksi();
                            }
                        }
                        $tekstemplate = str_replace("%nomor-kwitansi%", $nomorTransaksi, $tekstemplate);

                        $counter = 1;
                        $daftarBiayaDibayar = [];
                        foreach ($entity->getDaftarBiayaSekali() as $biaya) {
                            if ($counter > 3) {
                                $daftarBiayaDibayar[] = $this->get('translator')->trans('dll');
                                break;
                            }
                            $daftarBiayaDibayar[] = $biaya->getNama();
                            $counter++;
                        }
                        $tekstemplate = str_replace("%daftar-biaya%", (implode(", ", $daftarBiayaDibayar)), $tekstemplate);

                        $formatter = new \NumberFormatter($this->container->getParameter('locale'), \NumberFormatter::CURRENCY);
                        $symbol = $formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
                        $tekstemplate = str_replace(
                            "%besar-pembayaran%",
                            $symbol.". ".number_format($currentPaymentAmount, 0, ',', '.'),
                            $tekstemplate
                        );

                        if ($ponselOrtuWali != "") {
                            $nomorponsel = preg_split("/[\s,\/]+/", $ponselOrtuWali);
                            foreach ($nomorponsel as $ponsel) {
                                $messenger = $this->get('sisdik.messenger');
                                if ($messenger instanceof Messenger) {
                                    if ($vendorSekolah instanceof VendorSekolah) {
                                        if ($vendorSekolah->getJenis() == 'khusus') {
                                            $messenger->setUseVendor(true);
                                            $messenger->setVendorURL($vendorSekolah->getUrlPengirimPesan());
                                        }
                                    }
                                    $messenger->setPhoneNumber($ponsel);
                                    $messenger->setMessage($tekstemplate);
                                    $messenger->sendMessage($sekolah);
                                }
                            }
                        }
                    }
                }
            }

            if ($siswa->isLunasBiayaSekali()) {
                $pilihanLayananSms = $em->getRepository('LanggasSisdikBundle:PilihanLayananSms')
                    ->findOneBy([
                        'sekolah' => $sekolah,
                        'jenisLayanan' => 'zb-biaya-sekali-bayar-lunas',
                        'status' => true,
                    ])
                ;

                if ($pilihanLayananSms instanceof PilihanLayananSms) {
                    if ($pilihanLayananSms->getStatus()) {
                        $layanan = $em->getRepository('LanggasSisdikBundle:LayananSms')
                            ->findOneBy([
                                'sekolah' => $sekolah,
                                'jenisLayanan' => 'zb-biaya-sekali-bayar-lunas',
                            ])
                        ;
                        if ($layanan instanceof LayananSms) {
                            $tekstemplate = $layanan->getTemplatesms()->getTeks();

                            $namaOrtuWali = "";
                            $ponselOrtuWali = "";
                            $orangtuaWaliAktif = $siswa->getOrangtuaWaliAktif();
                            if ($orangtuaWaliAktif instanceof OrangtuaWali) {
                                $namaOrtuWali = $orangtuaWaliAktif->getNama();
                                $ponselOrtuWali = $orangtuaWaliAktif->getPonsel();
                            }

                            $tekstemplate = str_replace("%nama-ortuwali%", $namaOrtuWali, $tekstemplate);
                            $tekstemplate = str_replace("%nama-siswa%", $siswa->getNamaLengkap(), $tekstemplate);

                            $tekstemplate = str_replace("%daftar-biaya%", (implode(", ", $this->getDaftarBiayaSiswa($siswa))), $tekstemplate);

                            $formatter = new \NumberFormatter($this->container->getParameter('locale'), \NumberFormatter::CURRENCY);
                            $symbol = $formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
                            $tekstemplate = str_replace(
                                "%total-pembayaran%",
                                $symbol.". ".number_format($totalPayment, 0, ',', '.'),
                                $tekstemplate
                            );

                            if ($ponselOrtuWali != "") {
                                $nomorponsel = preg_split("/[\s,\/]+/", $ponselOrtuWali);
                                foreach ($nomorponsel as $ponsel) {
                                    $messenger = $this->get('sisdik.messenger');
                                    if ($messenger instanceof Messenger) {
                                        if ($vendorSekolah instanceof VendorSekolah) {
                                            if ($vendorSekolah->getJenis() == 'khusus') {
                                                $messenger->setUseVendor(true);
                                                $messenger->setVendorURL($vendorSekolah->getUrlPengirimPesan());
                                            }
                                        }
                                        $messenger->setPhoneNumber($ponsel);
                                        $messenger->setMessage($tekstemplate);
                                        $messenger->sendMessage($sekolah);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $this
                ->get('session')
                ->getFlashBag()
                ->add('success', $this->get('translator')->trans('flash.pembayaran.biaya.sekali.tersimpan'))
            ;

            return $this->redirect($this->generateUrl('pembayaran_biaya_sekali__summary', [
                'sid' => $sid,
            ]));
        }

        $this
            ->get('session')
            ->getFlashBag()
            ->add('error', $this->get('translator')->trans('flash.pembayaran.biaya.sekali.gagal.disimpan'))
        ;

        return [
            'entities' => $entities,
            'siswa' => $siswa,
            'itemBiayaSemua' => $itemBiaya['semua'],
            'itemBiayaTersimpan' => $itemBiaya['tersimpan'],
            'itemBiayaTersisa' => $itemBiaya['tersisa'],
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{sid}/{id}/show", name="pembayaran_biaya_sekali__show")
     * @Template()
     */
    public function showAction($sid, $id)
    {
        $this->setCurrentMenu();

        $em = $this->getDoctrine()->getManager();

        $siswa = $em->getRepository('LanggasSisdikBundle:Siswa')->find($sid);
        if (!(is_object($siswa) && $siswa instanceof Siswa)) {
            throw $this->createNotFoundException('Entity Siswa tak ditemukan.');
        }

        if ($this->get('security.authorization_checker')->isGranted('view', $siswa) === false) {
            throw new AccessDeniedException($this->get('translator')->trans('akses.ditolak'));
        }

        $entity = $em->getRepository('LanggasSisdikBundle:PembayaranSekali')->find($id);
        if (!(is_object($entity) && $entity instanceof PembayaranSekali)) {
            throw $this->createNotFoundException('Entity PembayaranSekali tak ditemukan.');
        }

        $daftarBiayaSekali = $entity->getDaftarBiayaSekali();
        $totalNominalTransaksiSebelumnya = $entity->getTotalNominalTransaksiPembayaranSekali();

        $nominalBiaya = 0;
        foreach ($entity->getDaftarBiayaSekali() as $daftar) {
            $nominalBiaya += $daftar->getNominal();
        }
        $adaPotongan = $entity->getAdaPotongan();
        $jenisPotongan = "";
        $nominalPotongan = 0;
        $persenPotongan = 0;
        if ($adaPotongan) {
            $jenisPotongan = $entity->getJenisPotongan();
            if ($jenisPotongan == 'nominal') {
                $nominalPotongan = $entity->getNominalPotongan();
            } elseif ($jenisPotongan == 'persentase') {
                $nominalPotongan = $nominalBiaya * ($entity->getPersenPotongan() / 100);
                $persenPotongan = $entity->getPersenPotongan();
            }
        }

        $transaksiPembayaran = $em->getRepository('LanggasSisdikBundle:TransaksiPembayaranSekali')
            ->findBy([
                'pembayaranSekali' => $id,
            ], [
                'waktuSimpan' => 'ASC',
            ])
        ;

        return [
            'siswa' => $siswa,
            'entity' => $entity,
            'totalNominalTransaksiSebelumnya' => $totalNominalTransaksiSebelumnya,
            'transaksiPembayaran' => $transaksiPembayaran,
            'nominalBiaya' => $nominalBiaya,
            'adaPotongan' => $adaPotongan,
            'jenisPotongan' => $jenisPotongan,
            'nominalPotongan' => $nominalPotongan,
            'persenPotongan' => $persenPotongan,
        ];
    }

    /**
     * Mengelola cicilan pembayaran biaya sekali bayar
     *
     * @Route("/{sid}/{id}/edit", name="pembayaran_biaya_sekali__edit")
     * @Template()
     */
    public function editAction($sid, $id)
    {
        $this->setCurrentMenu();

        $em = $this->getDoctrine()->getManager();

        $siswa = $em->getRepository('LanggasSisdikBundle:Siswa')->find($sid);
        if (!(is_object($siswa) && $siswa instanceof Siswa)) {
            throw $this->createNotFoundException('Entity Siswa tak ditemukan.');
        }

        if ($this->get('security.authorization_checker')->isGranted('edit', $siswa) === false) {
            throw new AccessDeniedException($this->get('translator')->trans('akses.ditolak'));
        }

        $entity = $em->getRepository('LanggasSisdikBundle:PembayaranSekali')->find($id);
        if (!(is_object($entity) && $entity instanceof PembayaranSekali)) {
            throw $this->createNotFoundException('Entity PembayaranSekali tak ditemukan.');
        }

        $daftarBiayaSekali = $entity->getDaftarBiayaSekali();
        if (count($daftarBiayaSekali) != 1) {
            throw new AccessDeniedException($this->get('translator')->trans('exception.biaya.sekali.bayar.gt.one'));
        }

        $totalNominalTransaksiSebelumnya = $entity->getTotalNominalTransaksiPembayaranSekali();

        $biaya = $daftarBiayaSekali->current();
        $nominalBiaya = $biaya->getNominal();
        $adaPotongan = $entity->getAdaPotongan();
        $jenisPotongan = "";
        $nominalPotongan = 0;
        $persenPotongan = 0;
        if ($adaPotongan) {
            $jenisPotongan = $entity->getJenisPotongan();
            if ($jenisPotongan == 'nominal') {
                $nominalPotongan = $entity->getNominalPotongan();
            } elseif ($jenisPotongan == 'persentase') {
                $nominalPotongan = $nominalBiaya * ($entity->getPersenPotongan() / 100);
                $persenPotongan = $entity->getPersenPotongan();
            }
        }

        if ($totalNominalTransaksiSebelumnya == ($nominalBiaya - $nominalPotongan) && $totalNominalTransaksiSebelumnya > 0) {
            throw new AccessDeniedException($this->get('translator')->trans('exception.biaya.sekali.bayar.lunas'));
        } else {
            $transaksiPembayaranSekali = new TransaksiPembayaranSekali();
            $entity->getTransaksiPembayaranSekali()->add($transaksiPembayaranSekali);

            $editForm = $this->createForm('sisdik_pembayaransekalicicilan', $entity);

            return [
                'siswa' => $siswa,
                'entity' => $entity,
                'totalNominalTransaksiSebelumnya' => $totalNominalTransaksiSebelumnya,
                'nominalBiaya' => $nominalBiaya,
                'adaPotongan' => $adaPotongan,
                'jenisPotongan' => $jenisPotongan,
                'nominalPotongan' => $nominalPotongan,
                'persenPotongan' => $persenPotongan,
                'edit_form' => $editForm->createView(),
            ];
        }
    }

    /**
     * @Route("/{sid}/{id}/update", name="pembayaran_biaya_sekali__update")
     * @Method("POST")
     * @Template("LanggasSisdikBundle:PembayaranSekali:edit.html.twig")
     */
    public function updateAction($sid, $id)
    {
        $sekolah = $this->getSekolah();
        $this->setCurrentMenu();

        $em = $this->getDoctrine()->getManager();

        $siswa = $em->getRepository('LanggasSisdikBundle:Siswa')->find($sid);
        if (!(is_object($siswa) && $siswa instanceof Siswa)) {
            throw $this->createNotFoundException('Entity Siswa tak ditemukan.');
        }

        if ($this->get('security.authorization_checker')->isGranted('edit', $siswa) === false) {
            throw new AccessDeniedException($this->get('translator')->trans('akses.ditolak'));
        }

        // total payment start here because of the unknown behavior during submitting request
        $totalPayment = $siswa->getTotalNominalPembayaranSekali();

        $entity = $em->getRepository('LanggasSisdikBundle:PembayaranSekali')->find($id);
        if (!(is_object($entity) && $entity instanceof PembayaranSekali)) {
            throw $this->createNotFoundException('Entity PembayaranSekali tak ditemukan.');
        }

        $transaksiSebelumnya = [];

        /* @var $transaksi TransaksiPembayaranSekali */
        foreach ($entity->getTransaksiPembayaranSekali() as $transaksi) {
            $tmp['sekolah'] = $transaksi->getSekolah();
            $tmp['dibuatOleh'] = $transaksi->getDibuatOleh();
            $tmp['nominalPembayaran'] = $transaksi->getNominalPembayaran();
            $tmp['keterangan'] = $transaksi->getKeterangan();

            $transaksiSebelumnya[] = $tmp;
        }

        $itemBiaya = $this->getBiayaProperties($siswa);

        $daftarBiayaSekali = $entity->getDaftarBiayaSekali();
        if (count($daftarBiayaSekali) != 1) {
            throw new AccessDeniedException($this->get('translator')->trans('exception.biaya.sekali.bayar.gt.one'));
        }

        $totalNominalTransaksiSebelumnya = $entity->getTotalNominalTransaksiPembayaranSekali();

        $nominalBiaya = $daftarBiayaSekali[0]->getNominal();
        $adaPotongan = $entity->getAdaPotongan();
        $jenisPotongan = "";
        $nominalPotongan = 0;
        $persenPotongan = 0;
        if ($adaPotongan) {
            $jenisPotongan = $entity->getJenisPotongan();
            if ($jenisPotongan == 'nominal') {
                $nominalPotongan = $entity->getNominalPotongan();
            } elseif ($jenisPotongan == 'persentase') {
                $nominalPotongan = $nominalBiaya * ($entity->getPersenPotongan() / 100);
                $persenPotongan = $entity->getPersenPotongan();
            }
        }

        if ($totalNominalTransaksiSebelumnya == ($nominalBiaya - $nominalPotongan) && $totalNominalTransaksiSebelumnya > 0) {
            throw new AccessDeniedException($this->get('translator')->trans('exception.biaya.sekali.bayar.telah.lunas'));
        }

        $editForm = $this->createForm('sisdik_pembayaransekalicicilan', $entity);
        $editForm->submit($this->getRequest());

        if ($editForm->isValid()) {
            $now = new \DateTime();

            $qbmaxnum = $em->createQueryBuilder()
                ->select('MAX(transaksi.nomorUrutTransaksiPerbulan)')
                ->from('LanggasSisdikBundle:TransaksiPembayaranSekali', 'transaksi')
                ->where("YEAR(transaksi.waktuSimpan) = :tahunsimpan")
                ->andWhere("MONTH(transaksi.waktuSimpan) = :bulansimpan")
                ->andWhere('transaksi.sekolah = :sekolah')
                ->setParameter('tahunsimpan', $now->format('Y'))
                ->setParameter('bulansimpan', $now->format('m'))
                ->setParameter('sekolah', $sekolah)
            ;
            $nomormax = intval($qbmaxnum->getQuery()->getSingleScalarResult());
            $nomormax++;

            foreach ($transaksiSebelumnya as $value) {
                $transaksi = $entity->getTransaksiPembayaranSekali()->current();

                $transaksi->setSekolah($value['sekolah']);
                $transaksi->setDibuatOleh($value['dibuatOleh']);
                $transaksi->setNominalPembayaran($value['nominalPembayaran']);
                $transaksi->setKeterangan($value['keterangan']);

                $entity->getTransaksiPembayaranSekali()->next();
            }

            $currentPaymentAmount = 0;
            $transaksi = $entity->getTransaksiPembayaranSekali()->last();
            if ($transaksi instanceof TransaksiPembayaranSekali) {
                $currentPaymentAmount = $transaksi->getNominalPembayaran();
                $transaksi->setNomorUrutTransaksiPerbulan($nomormax);

                if ($sekolah->getAtributNomorTransaksiBiayaSekali() !== null) {
                    $nomorTransaksiSekali = $sekolah->getAtributNomorTransaksiBiayaSekali();

                    $nomorTransaksiSekali = str_replace("%tahun%", $now->format('Y'), $nomorTransaksiSekali);
                    $nomorTransaksiSekali = str_replace("%bulan%", $now->format('m'), $nomorTransaksiSekali);
                    $nomorTransaksiSekali = str_replace("%tanggal%", $now->format('d'), $nomorTransaksiSekali);

                    $tmpNomorTransaksi = $nomormax;

                    $matches = [];
                    $penambah = preg_match('/{\+(\d+)}/', $nomorTransaksiSekali, $matches);
                    if ($penambah === 1) {
                        $tmpNomorTransaksi = $tmpNomorTransaksi + $matches[1];
                    }
                    $nomorTransaksiSekali = preg_replace('/{\+\d+}/', '', $nomorTransaksiSekali);

                    if (preg_match('/#+%nomor-urut-perbulan%/', $nomorTransaksiSekali) === 1) {
                        $placeholder = preg_match_all('/#/', $nomorTransaksiSekali);
                        if ($placeholder >= 1 && strlen($tmpNomorTransaksi) <= $placeholder) {
                            $tmpNomorTransaksi = str_repeat('0', $placeholder - strlen($tmpNomorTransaksi) + 1).$tmpNomorTransaksi;
                        }
                    }
                    $nomorTransaksiSekali = str_replace('#', '', $nomorTransaksiSekali);

                    $nomorTransaksiSekali = str_replace("%nomor-urut-perbulan%", $tmpNomorTransaksi, $nomorTransaksiSekali);

                    $transaksi->setNomorTransaksi($nomorTransaksiSekali);
                } else {
                    $transaksi->setNomorTransaksi(TransaksiPembayaranSekali::tandakwitansi.$now->format('Y').$now->format('m').$nomormax);
                }
            }

            $entity->setNominalTotalTransaksi($entity->getNominalTotalTransaksi() + $currentPaymentAmount);

            $payableAmountDue = $siswa->getTotalNominalBiayaSekali();
            $payableAmountRemain = $this->getPayableFeesRemain(
                $siswa->getTahun(),
                $itemBiaya['tersisa'],
                $siswa->getPenjurusan()
            );

            $totalPayment = $totalPayment + $currentPaymentAmount;
            $totalDiscount = $siswa->getTotalPotonganPembayaranSekali();

            if (($payableAmountRemain + $payableAmountDue) == ($totalPayment + $totalDiscount)) {
                $siswa->setLunasBiayaSekali(true);
            }

            // print("\$totalPayment: $totalPayment<br />");
            // print("\$totalDiscount: $totalDiscount<br />");
            // print("\$payableAmountDue: $payableAmountDue<br />");
            // print("\$payableAmountRemain: $payableAmountRemain<br />");
            // exit;

            $em->persist($entity);
            $em->persist($siswa);

            $em->flush();

            $vendorSekolah = $em->getRepository('LanggasSisdikBundle:VendorSekolah')
                ->findOneBy([
                    'sekolah' => $sekolah,
                ])
            ;

            $pilihanLayananSms = $em->getRepository('LanggasSisdikBundle:PilihanLayananSms')
                ->findOneBy([
                    'sekolah' => $sekolah,
                    'jenisLayanan' => 'za-biaya-sekali-bayar',
                    'status' => true,
                ])
            ;

            if ($pilihanLayananSms instanceof PilihanLayananSms) {
                if ($pilihanLayananSms->getStatus()) {
                    $layanan = $em->getRepository('LanggasSisdikBundle:LayananSms')
                        ->findOneBy([
                            'sekolah' => $sekolah,
                            'jenisLayanan' => 'za-biaya-sekali-bayar',
                        ])
                    ;
                    if ($layanan instanceof LayananSms) {
                        $tekstemplate = $layanan->getTemplatesms()->getTeks();

                        $namaOrtuWali = "";
                        $ponselOrtuWali = "";
                        $orangtuaWaliAktif = $siswa->getOrangtuaWaliAktif();
                        if ($orangtuaWaliAktif instanceof OrangtuaWali) {
                            $namaOrtuWali = $orangtuaWaliAktif->getNama();
                            $ponselOrtuWali = $orangtuaWaliAktif->getPonsel();
                        }

                        $tekstemplate = str_replace("%nama-ortuwali%", $namaOrtuWali, $tekstemplate);
                        $tekstemplate = str_replace("%nama-siswa%", $siswa->getNamaLengkap(), $tekstemplate);

                        $nomorTransaksi = "";
                        $em->refresh($entity);
                        foreach ($entity->getTransaksiPembayaranSekali() as $transaksi) {
                            if ($transaksi instanceof TransaksiPembayaranSekali) {
                                $em->refresh($transaksi);
                                $nomorTransaksi = $transaksi->getNomorTransaksi();
                            }
                        }
                        $tekstemplate = str_replace("%nomor-kwitansi%", $nomorTransaksi, $tekstemplate);

                        $counter = 1;
                        $daftarBiayaDibayar = [];
                        foreach ($entity->getDaftarBiayaSekali() as $biaya) {
                            if ($counter > 3) {
                                $daftarBiayaDibayar[] = $this->get('translator')->trans('dll');
                                break;
                            }
                            $daftarBiayaDibayar[] = $biaya->getNama();
                            $counter++;
                        }
                        $tekstemplate = str_replace("%daftar-biaya%", (implode(", ", $daftarBiayaDibayar)), $tekstemplate);

                        $formatter = new \NumberFormatter($this->container->getParameter('locale'), \NumberFormatter::CURRENCY);
                        $symbol = $formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
                        $tekstemplate = str_replace(
                            "%besar-pembayaran%",
                            $symbol.". ".number_format($currentPaymentAmount, 0, ',', '.'),
                            $tekstemplate
                        );

                        if ($ponselOrtuWali != "") {
                            $nomorponsel = preg_split("/[\s,\/]+/", $ponselOrtuWali);
                            foreach ($nomorponsel as $ponsel) {
                                $messenger = $this->get('sisdik.messenger');
                                if ($messenger instanceof Messenger) {
                                    if ($vendorSekolah instanceof VendorSekolah) {
                                        if ($vendorSekolah->getJenis() == 'khusus') {
                                            $messenger->setUseVendor(true);
                                            $messenger->setVendorURL($vendorSekolah->getUrlPengirimPesan());
                                        }
                                    }
                                    $messenger->setPhoneNumber($ponsel);
                                    $messenger->setMessage($tekstemplate);
                                    $messenger->sendMessage($sekolah);
                                }
                            }
                        }
                    }
                }
            }

            if ($siswa->isLunasBiayaSekali()) {
                $pilihanLayananSms = $em->getRepository('LanggasSisdikBundle:PilihanLayananSms')
                    ->findOneBy([
                        'sekolah' => $sekolah,
                        'jenisLayanan' => 'zb-biaya-sekali-bayar-lunas',
                        'status' => true,
                    ])
                ;

                if ($pilihanLayananSms instanceof PilihanLayananSms) {
                    if ($pilihanLayananSms->getStatus()) {
                        $layanan = $em->getRepository('LanggasSisdikBundle:LayananSms')
                            ->findOneBy([
                                'sekolah' => $sekolah,
                                'jenisLayanan' => 'zb-biaya-sekali-bayar-lunas',
                            ])
                        ;
                        if ($layanan instanceof LayananSms) {
                            $tekstemplate = $layanan->getTemplatesms()->getTeks();

                            $namaOrtuWali = "";
                            $ponselOrtuWali = "";
                            $orangtuaWaliAktif = $siswa->getOrangtuaWaliAktif();
                            if ($orangtuaWaliAktif instanceof OrangtuaWali) {
                                $namaOrtuWali = $orangtuaWaliAktif->getNama();
                                $ponselOrtuWali = $orangtuaWaliAktif->getPonsel();
                            }

                            $tekstemplate = str_replace("%nama-ortuwali%", $namaOrtuWali, $tekstemplate);
                            $tekstemplate = str_replace("%nama-siswa%", $siswa->getNamaLengkap(), $tekstemplate);

                            $tekstemplate = str_replace("%daftar-biaya%", (implode(", ", $this->getDaftarBiayaSiswa($siswa))), $tekstemplate);

                            $formatter = new \NumberFormatter($this->container->getParameter('locale'), \NumberFormatter::CURRENCY);
                            $symbol = $formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
                            $tekstemplate = str_replace(
                                "%total-pembayaran%",
                                $symbol.". ".number_format($totalPayment, 0, ',', '.'),
                                $tekstemplate
                            );

                            if ($ponselOrtuWali != "") {
                                $nomorponsel = preg_split("/[\s,\/]+/", $ponselOrtuWali);
                                foreach ($nomorponsel as $ponsel) {
                                    $messenger = $this->get('sisdik.messenger');
                                    if ($messenger instanceof Messenger) {
                                        if ($vendorSekolah instanceof VendorSekolah) {
                                            if ($vendorSekolah->getJenis() == 'khusus') {
                                                $messenger->setUseVendor(true);
                                                $messenger->setVendorURL($vendorSekolah->getUrlPengirimPesan());
                                            }
                                        }
                                        $messenger->setPhoneNumber($ponsel);
                                        $messenger->setMessage($tekstemplate);
                                        $messenger->sendMessage($sekolah);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $this
                ->get('session')
                ->getFlashBag()
                ->add('success', $this->get('translator')->trans('flash.pembayaran.cicilan.biaya.sekali.bayar.terbarui'))
            ;

            return $this->redirect($this->generateUrl('pembayaran_biaya_sekali__show', [
                'sid' => $sid,
                'id' => $id,
            ]));
        }

        $this
            ->get('session')
            ->getFlashBag()
            ->add('error', $this->get('translator')->trans('flash.pembayaran.cicilan.biaya.sekali.bayar.gagal.disimpan'))
        ;

        return [
            'siswa' => $siswa,
            'entity' => $entity,
            'totalNominalTransaksiSebelumnya' => $totalNominalTransaksiSebelumnya,
            'nominalBiaya' => $nominalBiaya,
            'adaPotongan' => $adaPotongan,
            'jenisPotongan' => $jenisPotongan,
            'nominalPotongan' => $nominalPotongan,
            'persenPotongan' => $persenPotongan,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * @param Siswa   $siswa
     * @param integer $max
     *
     * @return array
     */
    private function getDaftarBiayaSiswa(Siswa $siswa, $max = 4)
    {
        $em = $this->getDoctrine()->getManager();

        if ($siswa->getPenjurusan() instanceof Penjurusan) {
            $biayaSekali = $em->createQueryBuilder()
                ->select('biaya')
                ->from('LanggasSisdikBundle:BiayaSekali', 'biaya')
                ->where('biaya.tahun = :tahun')
                ->andWhere('biaya.penjurusan IS NULL OR biaya.penjurusan = :penjurusan')
                ->orderBy('biaya.urutan', 'ASC')
                ->setParameter('tahun', $siswa->getTahun())
                ->setParameter('penjurusan', $siswa->getPenjurusan())
                ->getQuery()
                ->getResult()
            ;
        } else {
            $biayaSekali = $em->createQueryBuilder()
                ->select('biaya')
                ->from('LanggasSisdikBundle:BiayaSekali', 'biaya')
                ->where('biaya.tahun = :tahun')
                ->andWhere('biaya.penjurusan IS NULL')
                ->orderBy('biaya.urutan', 'ASC')
                ->setParameter('tahun', $siswa->getTahun())
                ->getQuery()
                ->getResult()
            ;
        }

        $daftarBiaya = [];
        $counter = 1;
        foreach ($biayaSekali as $biaya) {
            if ($biaya instanceof BiayaSekali) {
                if ($counter > $max) {
                    $daftarBiaya[] = $this->get('translator')->trans('dll');
                    break;
                }
                $daftarBiaya[] = $biaya->getJenisbiaya()->getNama();
            }
            $counter++;
        }

        return $daftarBiaya;
    }

    /**
     * Mengambil identitas biaya sekali bayar seorang siswa
     *
     * @param Siswa $siswa
     * @return
     *                     array['semua'] array id biaya sekali bayar seluruhnya<br>
     *                     array['tersimpan'] array id biaya sekali bayar yang tersimpan<br>
     *                     array['tersisa'] array id biaya sekali bayar yang tersisa<br>
     */
    private function getBiayaProperties(Siswa $siswa)
    {
        $em = $this->getDoctrine()->getManager();

        if ($siswa->getPenjurusan() instanceof Penjurusan) {
            $biayaSekali = $em->createQueryBuilder()
                ->select('biaya')
                ->from('LanggasSisdikBundle:BiayaSekali', 'biaya')
                ->where('biaya.tahun = :tahun')
                ->andWhere('biaya.penjurusan IS NULL OR biaya.penjurusan = :penjurusan')
                ->orderBy('biaya.urutan', 'ASC')
                ->setParameter('tahun', $siswa->getTahun())
                ->setParameter('penjurusan', $siswa->getPenjurusan())
                ->getQuery()
                ->getResult()
            ;
        } else {
            $biayaSekali = $em->createQueryBuilder()
                ->select('biaya')
                ->from('LanggasSisdikBundle:BiayaSekali', 'biaya')
                ->where('biaya.tahun = :tahun')
                ->andWhere('biaya.penjurusan IS NULL')
                ->orderBy('biaya.urutan', 'ASC')
                ->setParameter('tahun', $siswa->getTahun())
                ->getQuery()
                ->getResult()
            ;
        }

        $idBiayaSemua = [];
        foreach ($biayaSekali as $biaya) {
            if ($biaya instanceof BiayaSekali) {
                $idBiayaSemua[] = $biaya->getId();
            }
        }

        $querybuilder1 = $em->createQueryBuilder()
            ->select('daftar')
            ->from('LanggasSisdikBundle:DaftarBiayaSekali', 'daftar')
            ->leftJoin('daftar.biayaSekali', 'biaya')
            ->leftJoin('daftar.pembayaranSekali', 'pembayaran')
            ->where('pembayaran.siswa = :siswa')
            ->orderBy('biaya.urutan', 'ASC')
            ->setParameter('siswa', $siswa)
        ;
        $daftarBiaya = $querybuilder1->getQuery()->getResult();
        $idBiayaTersimpan = [];
        foreach ($daftarBiaya as $daftar) {
            if ($daftar instanceof DaftarBiayaSekali) {
                $idBiayaTersimpan[] = $daftar->getBiayaSekali()->getId();
            }
        }

        $idBiayaSisa = array_diff($idBiayaSemua, $idBiayaTersimpan);

        return [
            'semua' => $idBiayaSemua,
            'tersimpan' => $idBiayaTersimpan,
            'tersisa' => $idBiayaSisa,
        ];
    }

    /**
     * Mengambil jumlah biaya sekali bayar yang tersisa
     *
     * @param Tahun      $tahun
     * @param array      $remainfee
     * @param Penjurusan $penjurusan
     *
     * @return integer
     */
    private function getPayableFeesRemain(Tahun $tahun, array $remainfee, Penjurusan $penjurusan = null)
    {
        $em = $this->getDoctrine()->getManager();

        if (is_array($remainfee) && count($remainfee) > 0) {
            if ($penjurusan instanceof Penjurusan) {
                $querybuilder = $em->createQueryBuilder()
                    ->select('biaya')
                    ->from('LanggasSisdikBundle:BiayaSekali', 'biaya')
                    ->where('biaya.tahun = :tahun')
                    ->andWhere('biaya.penjurusan IS NULL OR biaya.penjurusan = :penjurusan')
                    ->andWhere('biaya.id IN (?1)')
                    ->setParameter('tahun', $tahun)
                    ->setParameter('penjurusan', $penjurusan)
                    ->setParameter(1, $remainfee)
                ;
            } else {
                $querybuilder = $em->createQueryBuilder()
                    ->select('biaya')
                    ->from('LanggasSisdikBundle:BiayaSekali', 'biaya')
                    ->where('biaya.tahun = :tahun')
                    ->andWhere('biaya.penjurusan IS NULL')
                    ->andWhere('biaya.id IN (?1)')
                    ->setParameter('tahun', $tahun)
                    ->setParameter(1, $remainfee)
                ;
            }
            $entities = $querybuilder->getQuery()->getResult();

            $feeamount = 0;
            foreach ($entities as $entity) {
                if ($entity instanceof BiayaSekali) {
                    $feeamount += $entity->getNominal();
                }
            }
        } else {
            $feeamount = 0;
        }

        return $feeamount;
    }

    private function setCurrentMenu()
    {
        $translator = $this->get('translator');

        $menu = $this->container->get('langgas_sisdik.menu.main');
        $menu[$translator->trans('headings.payments', [], 'navigations')][$translator->trans('links.pembayaran.biaya.sekali.bayar', [], 'navigations')]->setCurrent(true);
    }

    /**
     * @return Sekolah
     */
    private function getSekolah()
    {
        return $this->getUser()->getSekolah();
    }
}
