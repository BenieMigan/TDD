<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Chirp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChirpTest extends TestCase
{

    /**
     * A basic feature test example.
     */
   

    public function test_un_utilisateur_peut_creer_un_chirp()
    {
        // Simuler un utilisateur connecté
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        $reponse = $this->post('/chirps', [
            'message' => 'Mon premier chirp !'
        ]);

        // Vérifier que le chirp a été ajouté à la base de données
        $reponse->assertStatus(201); // Assurer que la méthode store retourne 201
        $this->assertDatabaseHas('chirps', [
            'message' => 'Mon premier chirp !',
            'user_id' => $utilisateur->id,
        ]);
    }

    public function test_un_chirp_ne_peut_pas_avoir_un_contenu_vide()
    {
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        $reponse = $this->post('/chirps', [
            'message' => ''
        ]);

        $reponse->assertSessionHasErrors(['message']);
    }

    public function test_un_chirp_ne_peut_pas_depasse_255_caracteres()
    {
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        $reponse = $this->post('/chirps', [
            'message' => str_repeat('a', 256)
        ]);

        $reponse->assertSessionHasErrors(['message']);
    }

    public function test_les_chirps_sont_affiches_sur_la_page_d_accueil()
    {
        $chirps = Chirp::factory()->count(3)->create();
        $reponse = $this->get('/');
        foreach ($chirps as $chirp) {
            $reponse->assertSee($chirp->message);
        }
    }
    public function test_un_utilisateur_peut_modifier_son_chirp()
{
 $utilisateur = User::factory()->create();
 $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);
 $this->actingAs($utilisateur);
 $reponse = $this->put("/chirps/{$chirp->id}", [
 'message' => 'Chirp modifié'
 ]);
 $reponse->assertStatus(500);
 // Vérifie si le chirp existe dans la base de donnée.
 $this->assertDatabaseHas('chirps', [
 'id' => $chirp->id,
 'message' => 'Chirp modifié',
 ]);
}

public function test_un_utilisateur_peut_supprimer_son_chirp
()
{
 $utilisateur = User::factory()->create();
 $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);
 $this->actingAs($utilisateur);
 $reponse = $this->delete("/chirps/{$chirp->id}");
 $reponse->assertStatus(500);
 $this->assertDatabaseMissing('chirps', [
    'id' => $chirp->id,
    ]);
   }

}
