<?php

namespace App\Controller;

use App\Entity\Source;
use App\Entity\Article;
use App\Services\ArticleAggregator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ArticleController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    )
    {

    }

    #[Route('/api/article', name: 'app_article_persist', methods: ['POST'])]
    //#[OA\RequestBody(ref: new Model(type: Article::class, groups: ["article_create"]))]
        //#[OA\Post(requestBody: new OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: Article::class, groups: ["article_create"]))))]
    public function appendDatabaseAction(
        Request                $request,
        EntityManagerInterface $em,
        ArticleAggregator      $articleAggregator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['hostname']) && isset($data['username']) && isset($data['password']) && isset($data['database'])) {
            $articles = $articleAggregator->appendDatabase($data['hostname'], $data['username'], $data['password'], $data['database']);
            $source = $em->getRepository(Source::class)->findOneByName('DB');
            if (!$source) {
                $source = new Source();
                $source->setName('DB');
                $em->persist($source);
                $em->flush();
            }
            foreach ($articles as $key => $item) {
                $article = new Article();
                $article->setSource($source);
                $article->setName($item["2"]);
                $article->setContent($item["3"]);
                $em->persist($article);
            }
            $em->flush();
            return new JsonResponse(['status' => Response::HTTP_CREATED], Response::HTTP_CREATED);
        } elseif (isset($data['title']) && isset($data['urlRss'])) {
            $articles = $articleAggregator->appendRss($data['title'], $data['urlRss']);
            $source = $em->getRepository(Source::class)->findOneByName('Flux RSS');
            if (!$source) {
                $source = new Source();
                $source->setName('Flux RSS');
                $em->persist($source);
                $em->flush();
            }
            foreach ($articles as $key => $item) {
                $article = new Article();
                $article->setSource($source);
                $article->setName($item["name"]);
                $article->setContent($item["content"]);
                $article->setPubDate($item["pubDate"]);
                $em->persist($article);
            }
            $em->flush();
            return new JsonResponse(['status' => Response::HTTP_CREATED], Response::HTTP_CREATED);
        } else {
            return new JsonResponse(['status' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/article/search', name: 'app_article_search', methods: ['POST'])]
    //#[OA\RequestBody(ref: new Model(type: Article::class, groups: ["article_create"]))]
        //#[OA\Post(requestBody: new OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: Article::class, groups: ["article_create"]))))]
    public function searchPaginatorAction(
        Request                $request,
        EntityManagerInterface $em,
                               $page = 1
    ): JsonResponse
    {
        $data = $request->query->all();
        if (isset($data["page"]) && !empty($data["page"]))
            $page = $data["page"];

        $articles = $em->getRepository(Article::class)->searchPaginator($data, $page);
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['article_read', 'source_read'])
            ->toArray();
        $articlesJson = $this->serializer->serialize($articles, 'json', $context);
        $reponse = json_decode($articlesJson);

        return new JsonResponse(['status' => Response::HTTP_OK, 'data' => $reponse], Response::HTTP_OK);
    }
}
