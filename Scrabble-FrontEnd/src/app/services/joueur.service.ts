import {Injectable} from '@angular/core';
import {HttpClient, HttpHeaders} from "@angular/common/http";
import {Joueur} from "../model/joueur.model";
import {Router} from "@angular/router";

@Injectable({
  providedIn: 'root'
})
export class JoueurService {

  private InscriptionAPI = "http://localhost:8000/api/v1/inscrire";
  private quitGameAPI = "http://localhost:8000/api/v1/quitter/joueur/";
  public messageError: any;
  public isError: any = false;



  constructor(private http: HttpClient, private router :Router) { }
  public addPlayer(joueur: any) {
    return this.http.post<any>(this.InscriptionAPI, joueur);
  }
  public quitGame(id: any) {
    const headers = new HttpHeaders();
    headers.append('Content-Type', 'multipart/form-data');
    headers.append('Accept', 'application/json');
    return this.http.get<any>(this.quitGameAPI+ id,{headers: headers});
  }
  inscrire(joueur : any){
    this.addPlayer(joueur).subscribe(data => {
      this.router.navigate(['/room']);
      localStorage.setItem('idJoueur',data.idJoueur);
    });

  }
}
